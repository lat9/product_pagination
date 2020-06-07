<?php
// -----
// Part of the "Product Pagination" plugin by lat9 (lat9@vinosdefrutastropicales.com)
// Copyright (c) 2010-2020 Vinos de Frutas Tropicales
// 
if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

// -----
// This class, included by the main split_pages_result.php class when the plugin is enabled, overrides the name/handling of that
// base class.  It's active ONLY WHEN the plugin has been configured to provide processing on "other", non-product-details type pages
// and the current page is in that configuration.
//
class splitPageResults extends base 
{
    protected $current_page_number, 
              $number_of_rows_per_page, 
              $page_name;
    public    $sql_query,
              $number_of_rows,
              $number_of_pages;
    
    public function __construct($query, $max_rows, $count_key = '*', $page_holder = 'page', $debug = false, $countQuery = '') 
    {
        global $db;
        
        $this->debug = array();
    
        $max_rows = ($max_rows == '' || $max_rows <= 0) ? 20 : $max_rows;
        $this->minimum_rows = $max_rows;
        
        $this->input_page_suffix = 1;
        $this->input_pagecount_suffix = 1;
        $this->hidden_var_added = false;
        
        // -----
        // If the plugin has been configured to provide an items-per-page dropdown ...
        //
        if (PRODUCTS_PAGINATION_PRODUCT_COUNT == 'true') {    
            $page_count_array = explode (',', PRODUCTS_PAGINATION_COUNT_VALUES);
            if (count($page_count_array) > 0) {
                sort($page_count_array, SORT_NUMERIC);
                if ($page_count_array[0] != '*' && ((int)$page_count_array[0]) >= 0) { 
                    $this->minimum_rows = $page_count_array[0];
                } elseif (count ($page_count_array) > 1 && $page_count_array[1] != '*' && ((int)$page_count_array[1]) >= 0) {
                    $this->minimum_rows = $page_count_array[1];
                }
            }

            // -----
            // Since the pagecount value is now a $_GET variable, need to sanitize its value prior to use, making sure
            // that the value is one of the values configured; otherwise, it's set to the minimum item count.
            //
            if (!empty($_GET['pagecount']) ) {
                if ($_GET['pagecount'] != 'all') {
                    if (!in_array($_GET['pagecount'], $page_count_array)) {
                        $_GET['pagecount'] = $this->minimum_rows;
                    }
                }
                $max_rows = $_GET['pagecount'];
                $pagecnt  = $max_rows;
            } else {
                $max_rows = $this->minimum_rows;
            }
        }

        $this->sql_query = preg_replace("/\n\r|\r\n|\n|\r/", ' ', $query);
        if ($countQuery != '') {
            $countQuery = preg_replace("/\n\r|\r\n|\n|\r/", ' ', $countQuery);
        }
        $this->countQuery = ($countQuery != '') ? $countQuery : $this->sql_query;
        $this->page_name = $page_holder;

        $this->debug[] = "Original query: $query";
        $this->debug[] = "Original count-query: $countQuery";
        $this->debug[] = "SQL query: " . $this->sql_query;
        $this->debug[] = "count query: " . $this->countQuery;
 
        if (isset($_GET[$page_holder])) {
            $page = $_GET[$page_holder];
        } elseif (isset($_POST[$page_holder])) {
            $page = $_POST[$page_holder];
        } else {
            $page = '';
        }

        if (empty($page) || !is_numeric($page) || $page < 0) {
            $page = 1;
        }
        $this->current_page_number = $page;

        $pos_to = strlen($this->countQuery);

        $query_lower = strtolower($this->countQuery);
        $pos_from = strpos($query_lower, ' from', 0);

        $pos_group_by = strpos($query_lower, ' group by', $pos_from);
        if ($pos_group_by !== false && $pos_group_by < $pos_to) {
            $pos_to = $pos_group_by;
        }

        $pos_having = strpos($query_lower, ' having', $pos_from);
        if ($pos_having !== false && $pos_having < $pos_to) {
            $pos_to = $pos_having;
        }

        $pos_order_by = strpos($query_lower, ' order by', $pos_from);
        if ($pos_order_by !== false && $pos_order_by < $pos_to) {
            $pos_to = $pos_order_by;
        }

        if ((strpos($query_lower, 'distinct') || strpos($query_lower, 'group by')) && $count_key != '*') {
            $count_string = 'distinct ' . zen_db_input ($count_key);
        } else {
            $count_string = zen_db_input($count_key);
        }
        $count_query = "SELECT count(" . $count_string . ") AS total " . substr($this->countQuery, $pos_from, $pos_to - $pos_from);
        
        $this->debug[] = "count_query = $count_query";

        $count = $db->Execute($count_query);

        // -----
        // Now that the items/page is a $_GET variable, need to do some additional sanitization.  There's a case where
        // the pagecount value has been forced to one of the configured values, but the current selection has fewer than
        // that number.  If that condition is found, force the items-per-page to the minimum value.
        //
        $this->number_of_rows_per_page = $max_rows;
        $this->number_of_rows = $count->fields['total'];
        if (isset($pagecnt)) {
            if ($pagecnt == 'all') {
                $this->number_of_rows_per_page = ($this->number_of_rows > 0) ? $this->number_of_rows : 20;
            } elseif ($pagecnt > $this->number_of_rows) {
                $this->number_of_rows_per_page = $this->minimum_rows;
                $_GET['pagecount'] = $this->minimum_rows;
            }
        }
        $this->number_of_pages = ceil($this->number_of_rows / $this->number_of_rows_per_page);

        if ($this->current_page_number > $this->number_of_pages) {
            $this->current_page_number = $this->number_of_pages;
        }

        $offset = $this->number_of_rows_per_page * ($this->current_page_number - 1);

        // fix offset error on some versions
        if ($offset <= 0) { 
            $offset = 0; 
        }

        $this->sql_query .= " LIMIT " . ($offset > 0 ? $offset . ", " : '') . $this->number_of_rows_per_page;
    }

    public function display_links($max_page_links, $parameters = '') 
    {
        global $request_type;
        if (empty($max_page_links)) {
            $max_page_links = 1;
        }

        $display_links_string = '';
        $class = '';

        if (zen_not_null($parameters) && substr($parameters, -1) != '&') {
            $parameters .= '&';
        }

        if ($this->number_of_pages > 1) {
            $ulClass = ' class="pagination-links"';
            if (PRODUCTS_PAGINATION_DISPLAY_PAGEDROP == 'true' || PRODUCTS_PAGINATION_PRODUCT_COUNT == 'true') {
                $ulClass = ' class="pp_float pagination-links"';
            }     
            $display_links_string .= '<ul' . $ulClass . '>';
            
            $display_links_string .= $this->formatPageLink(PREVNEXT_TITLE_PREVIOUS_PAGE, PP_TEXT_PREVIOUS, $parameters . $this->page_name . '=' . ($this->current_page_number - 1), ($this->current_page_number > 1) ? true : false, ' class="prevnext"');

            if ($this->number_of_pages <= PRODUCTS_PAGINATION_MAX) {
                for ($i = 1; $i <= $this->number_of_pages; $i++) {
                    $display_links_string .= $this->formatPageLink(sprintf(PREVNEXT_TITLE_PAGE_NO, $i), $i, $parameters . $this->page_name . '=' . $i, true, ($i == $this->current_page_number) ? ' class="currentpage"' : '');
                }
            } else {
                $current_page_index = $this->current_page_number - 1;
                $first_link = $current_page_index - floor(PRODUCTS_PAGINATION_MID_RANGE / 2);
                $last_link  = $current_page_index + floor(PRODUCTS_PAGINATION_MID_RANGE  /2);

                if ($first_link < 0) {
                    $last_link += abs($first_link);
                    $first_link = 0;
                }

                $last_page_index = $this->number_of_pages - 1 ;
                if ($last_link > $last_page_index) {
                    $first_link -= $last_link - $last_page_index;
                    $last_link   = $last_page_index;
                }
                $display_range = range($first_link, $last_link);

                for ($i = 0, $pNum = 1; $i < $this->number_of_pages; $i++, $pNum++) {
                    if ($display_range[0] > 1 && $i == $display_range[0]) {
                        $display_links_string .= '<li> ... </li>';
                    }
                    // loop through all pages. if first, last, or in range, display
                    if ($i == 0 || $i == $last_page_index || in_array($i, $display_range)) {
                        $display_links_string .= $this->formatPageLink(sprintf(PREVNEXT_TITLE_PAGE_NO, $pNum), $pNum, $parameters . $this->page_name . '=' . $pNum, true, ($pNum == $this->current_page_number) ? ' class="mid currentpage"' : ' class="mid"');
                    }

                    if ($display_range[PRODUCTS_PAGINATION_MID_RANGE-1] < $last_page_index-1 && $i == $display_range[PRODUCTS_PAGINATION_MID_RANGE-1]) {
                        $display_links_string .= '<li class="mid"> &hellip; </li>';
                    }

                }
            }
    
            $display_links_string .= $this->formatPageLink( PREVNEXT_TITLE_NEXT_PAGE, PP_TEXT_NEXT, $parameters . $this->page_name . '=' . ($this->current_page_number + 1), ($this->current_page_number == $this->number_of_pages) ? false : true, ' class="prevnext"');
            
            $display_links_string .= '</ul><div class="clearBoth"></div>';
        }
            
        $extra_links = '';
        if (PRODUCTS_PAGINATION_DISPLAY_PAGEDROP == 'true') {
            $extra_links .= $this->createPageDropdown ($this->number_of_pages, $this->current_page_number);
        }
        if (PRODUCTS_PAGINATION_PRODUCT_COUNT == 'true') {
            $extra_links .= $this->createCountDropdown ($this->number_of_rows, $this->number_of_rows_per_page);
        }
        if ($extra_links != '') {
            $extra_links = '<div class="pp-selections">' . $extra_links . '<div class="clearBoth"></div></div>';
        }

        if ($display_links_string != '' || $extra_links != '') {
            $display_links_string = '<div class="ppNextPrevWrapper"><div class="prod-pagination">' . $display_links_string . '</div>' . $extra_links . '<div class="clearBoth"></div></div>';
        }  

        return $display_links_string;
    }

    // display number of total products found
    public function display_count($text_output) 
    {
        $to_num = ($this->number_of_rows_per_page * $this->current_page_number);
        if ($to_num > $this->number_of_rows) {
            $to_num = $this->number_of_rows;
        }

        $from_num = $this->number_of_rows_per_page * ($this->current_page_number - 1);

        if ($to_num == 0) {
            $from_num = 0;
        } else {
            $from_num++;
        }

        return ($to_num <= 1) ? '' : sprintf($text_output, $from_num, $to_num, $this->number_of_rows);
    }

    private function formatPageLink($title, $name, $page_link_parms, $display_flag=true, $extra_class='') 
    {
        global $request_type;
        if ($display_flag) {
            $returnValue = '<li><a href="' . zen_href_link ($_GET['main_page'], $page_link_parms, $request_type, false) . '"' . $extra_class . ' title="' . htmlentities(zen_clean_html($title), ENT_COMPAT, CHARSET, true) . '">' . $name . '</a></li>';
        } else {
            $returnValue = '<li><span class="prevnext disablelink">' . $name . '</span></li>';
        }
        return $returnValue;
    } 

    private function createCountDropdown($numItems, $whichCount) 
    {
        $dropdown = '';
        $countArray = explode(',', PRODUCTS_PAGINATION_COUNT_VALUES);
        if (count($countArray) > 0) {
            $pageArray = array();
            for ($i = 0, $n = count($countArray), $done_all = false; $i < $n; $i++) {
                if ($countArray[$i] == '*') {
                    if (!$done_all) {
                        $pageArray[] = array('id' => 'all', 'text' => PP_TEXT_ALL);
                        $done_all = true;
                    }
                } elseif ($numItems > $countArray[$i]) {
                    $pageArray[] = array('id' => $countArray[$i], 'text' => $countArray[$i]);
                } elseif (!$done_all) {
                    $pageArray[] = array('id' => 'all', 'text' => PP_TEXT_ALL);
                    $done_all = true;
                }
            }

            //-----
            // Only display the dropdown if there's more than one item counts to display
            //    
            if (count($pageArray) > 1) {
                // -----
                // If called from either an listing or the advanced search results page and "multiple products add to cart" is
                // enabled, there's already a <form> active so don't want to insert a form into a form.
                //
                // Need to add that suffix to the dropdown's variable name in case both upper- and lower-links are enabled.  If not, then
                // both variables (top and bottom) would have the same variable name, rendering the top block useless.
                //
                if ($this->formCreated()) {
                    $form = '';
                    $end_form = '';
                    $var_name = 'pp_pagecount' . $this->input_pagecount_suffix;
                    $hidden_vars = ($this->hidden_var_added) ? '' : zen_draw_hidden_field('pp_which_input', '0', 'id="pp-which-input"');
                    $onchange = "document.getElementById('pp-which-input').value = 'pc-" . $this->input_pagecount_suffix . "'; ";
                    $this->hidden_var_added = true;
                } else {
                    $formPage = ($_GET['main_page'] == FILENAME_ADVANCED_SEARCH_RESULT) ? FILENAME_ADVANCED_SEARCH : $_GET['main_page'];
                    $form = zen_draw_form('pp_count_form', zen_href_link ($formPage, zen_get_all_get_params(array('pagecount'))), 'get');
                    $end_form = '</form>';
                    $var_name = 'pagecount';
                    $hidden_vars = $this->createHiddenVars('page');
                    $onchange = '';
                }
                $dropdown_id = 'id="pp-pc-' . $this->input_pagecount_suffix . '"';
          
                $whichCount = (isset($_GET['pagecount']) && $_GET['pagecount'] == 'all') ? 'all' : $whichCount;
          
                $dropdown  = PHP_EOL . '<div class="pp_count">' . $form . zen_hide_session_id();

                $dropdown .= $hidden_vars;
                $dropdown .= PP_TEXT_ITEMS_PER_PAGE . zen_draw_pull_down_menu($var_name, $pageArray, $whichCount, $dropdown_id . ' onchange="' . $onchange . 'this.form.submit();"') . $end_form . '</div>' . PHP_EOL;
                
                $this->input_pagecount_suffix++;
            }
        }
        return $dropdown;
    }
    
    private function createPageDropdown($lastPage, $current_page) 
    {
        $dropdown = '';
        if ($lastPage > 1) {
            $pageArray = array();
            for ($i = 1; $i <= $lastPage; $i++) {
                $pageArray[] = array('id' => $i, 'text' => $i);
            }
            // -----
            // If called from a page where the "multiple products add to cart" is enabled, the form that's
            // created is subtly different, since that form "could" have multiple instances (top/bottom) of the page dropdown.
            //
            // Need to add that suffix to the dropdown's variable name in case both upper- and lower-links are enabled.  If not, then
            // both variables (top and bottom) would have the same variable name, rendering the top block useless.
            //
            if ($this->formCreated()) {
                $form = '';
                $end_form = '';
                $var_name = 'pp_page' . $this->input_page_suffix;
                $hidden_vars = ($this->hidden_var_added) ? '' : zen_draw_hidden_field('pp_which_input', '0', 'id="pp-which-input"');
                $onchange = "document.getElementById('pp-which-input').value = 'p-" . $this->input_page_suffix . "'; ";
                $this->hidden_var_added = true;
            } else {
                $form = zen_draw_form('pp_page_form', zen_href_link($_GET['main_page'], zen_get_all_get_params(array('page'))), 'get');
                $end_form = '</form>';
                $var_name = 'page';
                $hidden_vars = $this->createHiddenVars('pagecount');
                $onchange = '';
            }
            $dropdown_id = 'id="pp-p-' . $this->input_page_suffix . '"';

            $dropdown = PHP_EOL . '<div class="pp_page">' . $form . PP_TEXT_PAGE . zen_draw_pull_down_menu($var_name, $pageArray, $current_page, $dropdown_id . ' onchange="' . $onchange . 'this.form.submit();"') . $hidden_vars . $end_form . '</div>' . PHP_EOL;
            
            $this->input_page_suffix++;
            
        }
        return $dropdown;
    }
    
    private function formCreated()
    {
        global $show_top_submit_button, $show_bottom_submit_button;
        return (!empty($show_top_submit_button) || !empty($show_bottom_submit_button));
    }
    
    private function createHiddenVars($additional_var = '')
    {
        $hidden_vars = ppHiddenVarsList();
        if ($additional_var != '') {
            $hidden_vars[] = $additional_var;
        }
        return ppCreateHiddenInputs($hidden_vars);
    }
}
