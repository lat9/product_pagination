<?php
/**
 * split_page_results Class.
 *
 * @package classes
 * @copyright Copyright 2003-2012 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: Author: Ian Wilson  Fri Aug 17 17:54:58 2012 +0100 Modified in v1.5.1 $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
/**
 * Split Page Result Class
 *
 * An sql paging class, that allows for sql reslt to be shown over a number of pages using  simple navigation system
 * Overhaul scheduled for subsequent release
 *
 * @package classes
 */
class splitPageResults extends base {
  var $sql_query, $number_of_rows, $current_page_number, $number_of_pages, $number_of_rows_per_page, $page_name;
  var $formSuffix; /*v1.4.5-a-lat9*/

  /* class constructor */
  function splitPageResults($query, $max_rows, $count_key = '*', $page_holder = 'page', $debug = false, $countQuery = "") {
    global $db;
    
    $formSuffix = ''; /*v1.4.5-a-lat9*/
    $max_rows = ($max_rows == '' || $max_rows == 0) ? 20 : $max_rows;

//-bof-lat9-Products Pagination-See if max-display override in place
    $this->minimum_rows = $max_rows;
    if (PRODUCTS_PAGINATION_OTHER == 'true' && in_array($_GET['main_page'], explode(',', PRODUCTS_PAGINATION_OTHER_MAIN_PAGES)) && PRODUCTS_PAGINATION_PRODUCT_COUNT == 'true') {    
      $sprCountArray = explode(',', PRODUCTS_PAGINATION_COUNT_VALUES);
      if (count($sprCountArray) > 0) {
        sort($sprCountArray, SORT_NUMERIC);
        if ($sprCountArray[0] != '*' && ((int)$sprCountArray[0]) != 0) {  /*v1.4.4-c-lat9*/
          $this->minimum_rows = $sprCountArray[0];
        } elseif (count($sprCountArray) > 1 && $sprCountArray[1] != '*' && ((int)$sprCountArray[1]) != 0) {  /*v1.4.4-c-lat9*/
          $this->minimum_rows = $sprCountArray[1];
        }
      }

      if (isset($_GET) && zen_not_null($_GET['pagecount'])) {
        $max_rows = $_GET['pagecount'];
        $pagecnt  = $max_rows;

      } else {
        $max_rows = $this->minimum_rows;
      }
    }
//-eof-lat9-Products Pagination

    $this->sql_query = preg_replace("/\n\r|\r\n|\n|\r/", " ", $query);
    if ($countQuery != "") $countQuery = preg_replace("/\n\r|\r\n|\n|\r/", " ", $countQuery);
    $this->countQuery = ($countQuery != "") ? $countQuery : $this->sql_query;
    $this->page_name = $page_holder;

    if ($debug) {
      echo '<br /><br />';
      echo 'original_query=' . $query . '<br /><br />';
      echo 'original_count_query=' . $countQuery . '<br /><br />';
      echo 'sql_query=' . $this->sql_query . '<br /><br />';
      echo 'count_query=' . $this->countQuery . '<br /><br />';
    }
    if (isset($_GET[$page_holder])) {
      $page = $_GET[$page_holder];
    } elseif (isset($_POST[$page_holder])) {
      $page = $_POST[$page_holder];
    } else {
      $page = '';
    }

    if (empty($page) || !is_numeric($page)) $page = 1;
    $this->current_page_number = $page;

    $this->number_of_rows_per_page = $max_rows;

    $pos_to = strlen($this->countQuery);

    $query_lower = strtolower($this->countQuery);
    $pos_from = strpos($query_lower, ' from', 0);

    $pos_group_by = strpos($query_lower, ' group by', $pos_from);
    if (($pos_group_by < $pos_to) && ($pos_group_by != false)) $pos_to = $pos_group_by;

    $pos_having = strpos($query_lower, ' having', $pos_from);
    if (($pos_having < $pos_to) && ($pos_having != false)) $pos_to = $pos_having;

    $pos_order_by = strpos($query_lower, ' order by', $pos_from);
    if (($pos_order_by < $pos_to) && ($pos_order_by != false)) $pos_to = $pos_order_by;

//-bof-product pagination
//    if (strpos($query_lower, 'distinct') || strpos($query_lower, 'group by')) {
    if ((strpos($query_lower, 'distinct') || strpos($query_lower, 'group by')) && $count_key != '*') {  /*v1.4.7c*/
//-eof-product_pagination
      $count_string = 'distinct ' . zen_db_input($count_key);
    } else {
      $count_string = zen_db_input($count_key);
    }
    $count_query = "select count(" . $count_string . ") as total " . substr($this->countQuery, $pos_from, ($pos_to - $pos_from));
    if ($debug) {
      echo 'count_query=' . $count_query . '<br /><br />';
    }
    $count = $db->Execute($count_query);
    
//-bof-lat9-Products Pagination
    $this->number_of_rows = $count->fields['total']; /*v1.4.3-c-lat9: Incorrect display when 0 rows returned*/
    if (isset($pagecnt) && $pagecnt == 'all') $this->number_of_rows_per_page = ($this->number_of_rows > 0) ? $this->number_of_rows : 20; /*v1.4.3-c-lat9: Don't allow divide-by-0 */
//-eof-lat9-Products Pagination
    $this->number_of_pages = ceil($this->number_of_rows / $this->number_of_rows_per_page);

    if ($this->current_page_number > $this->number_of_pages) {
      $this->current_page_number = $this->number_of_pages;
    }

    $offset = ($this->number_of_rows_per_page * ($this->current_page_number - 1));

    // fix offset error on some versions
    if ($offset <= 0) { $offset = 0; }

    $this->sql_query .= " limit " . ($offset > 0 ? $offset . ", " : '') . $this->number_of_rows_per_page;
  }

  /* class functions */

  // display split-page-number-links
  function display_links($max_page_links, $parameters = '') {
    global $request_type;
    $this->formSuffix = ($this->formSuffix == '') ? '1' : '2';  /*v1.4.5-a-lat9*/
    if ($max_page_links == '') $max_page_links = 1;

    $display_links_string = '';

    $class = '';

    if (zen_not_null($parameters) && (substr($parameters, -1) != '&')) $parameters .= '&';

//-bof-lat9 Products Pagination - Use original split pages' results format if not enabled or page not in list.
    if (PRODUCTS_PAGINATION_OTHER != 'true' || !in_array($_GET['main_page'], explode(',', PRODUCTS_PAGINATION_OTHER_MAIN_PAGES))) {
//-eof-lat9
      // previous button - not displayed on first page
      if ($this->current_page_number > 1) $display_links_string .= '<a href="' . zen_href_link($_GET['main_page'], $parameters . $this->page_name . '=' . ($this->current_page_number - 1), $request_type) . '" title=" ' . PREVNEXT_TITLE_PREVIOUS_PAGE . ' ">' . PREVNEXT_BUTTON_PREV . '</a>&nbsp;&nbsp;';

      // check if number_of_pages > $max_page_links
      $cur_window_num = intval($this->current_page_number / $max_page_links);
      if ($this->current_page_number % $max_page_links) $cur_window_num++;

      $max_window_num = intval($this->number_of_pages / $max_page_links);
      if ($this->number_of_pages % $max_page_links) $max_window_num++;

      // previous window of pages
      if ($cur_window_num > 1) $display_links_string .= '<a href="' . zen_href_link($_GET['main_page'], $parameters . $this->page_name . '=' . (($cur_window_num - 1) * $max_page_links), $request_type) . '" title=" ' . sprintf(PREVNEXT_TITLE_PREV_SET_OF_NO_PAGE, $max_page_links) . ' ">...</a>';

      // page nn button
      for ($jump_to_page = 1 + (($cur_window_num - 1) * $max_page_links); ($jump_to_page <= ($cur_window_num * $max_page_links)) && ($jump_to_page <= $this->number_of_pages); $jump_to_page++) {
        if ($jump_to_page == $this->current_page_number) {
          $display_links_string .= '&nbsp;<strong class="current">' . $jump_to_page . '</strong>&nbsp;';
        } else {
          $display_links_string .= '&nbsp;<a href="' . zen_href_link($_GET['main_page'], $parameters . $this->page_name . '=' . $jump_to_page, $request_type) . '" title=" ' . sprintf(PREVNEXT_TITLE_PAGE_NO, $jump_to_page) . ' ">' . $jump_to_page . '</a>&nbsp;';
        }
      }

      // next window of pages
      if ($cur_window_num < $max_window_num) $display_links_string .= '<a href="' . zen_href_link($_GET['main_page'], $parameters . $this->page_name . '=' . (($cur_window_num) * $max_page_links + 1), $request_type) . '" title=" ' . sprintf(PREVNEXT_TITLE_NEXT_SET_OF_NO_PAGE, $max_page_links) . ' ">...</a>&nbsp;';

      // next button
      if (($this->current_page_number < $this->number_of_pages) && ($this->number_of_pages != 1)) $display_links_string .= '&nbsp;<a href="' . zen_href_link($_GET['main_page'], $parameters . 'page=' . ($this->current_page_number + 1), $request_type) . '" title=" ' . PREVNEXT_TITLE_NEXT_PAGE . ' ">' . PREVNEXT_BUTTON_NEXT . '</a>&nbsp;';

//-bof-lat9 Products Pagination - Feature is enabled and current page in requested list.
    } else {
      if ($this->number_of_pages == 1) {
        $display_links_string .= '&nbsp;';

      } else {
        $ulClass    = ' class="pagination-links"';
      
        if (PRODUCTS_PAGINATION_DISPLAY_PAGEDROP == 'true' || PRODUCTS_PAGINATION_PRODUCT_COUNT == 'true') {
          $ulClass = ' class="pp_float pagination-links"';
        }
      
        $display_links_string .= '<ul' . $ulClass . '>';
        $display_links_string .= $this->formatPageLink (PREVNEXT_TITLE_PREVIOUS_PAGE, PP_TEXT_PREVIOUS, $parameters . $this->page_name . '=' . ($this->current_page_number - 1), ($this->current_page_number > 1) ? true : false, ' class="prevnext"');

        if ($this->number_of_pages <= PRODUCTS_PAGINATION_MAX) {
          for ($i=1; $i <= $this->number_of_pages; $i++) {
            $display_links_string .= $this->formatPageLink (sprintf(PREVNEXT_TITLE_PAGE_NO, $i), $i, $parameters . $this->page_name . '=' . $i, true, ($i == $this->current_page_number) ? ' class="currentpage"' : '');
          }

        } else {
          $current_page_index = $this->current_page_number - 1;
          $first_link = $current_page_index - floor(PRODUCTS_PAGINATION_MID_RANGE/2);
          $last_link  = $current_page_index + floor(PRODUCTS_PAGINATION_MID_RANGE/2);

          if($first_link < 0) {
            $last_link += abs($first_link);
            $first_link = 0;
          }

          $last_page_index = $this->number_of_pages - 1 ;
          if($last_link > $last_page_index) {
            $first_link -= $last_link - $last_page_index;
            $last_link   = $last_page_index;
          }
          $display_range = range($first_link, $last_link);

          for ($i=0, $pNum=1; $i < $this->number_of_pages; $i++, $pNum++) {
            if ($display_range[0] > 1 && $i == $display_range[0]) $display_links_string .= '<li> ... </li>';
            // loop through all pages. if first, last, or in range, display
            if ($i == 0 || $i == $last_page_index || in_array($i, $display_range)) {
              $display_links_string .= $this->formatPageLink(sprintf(PREVNEXT_TITLE_PAGE_NO, $pNum), $pNum, $parameters . $this->page_name . '=' . $pNum, true, ($pNum == $this->current_page_number) ? ' class="currentpage"' : '');
            }

            if ($display_range[PRODUCTS_PAGINATION_MID_RANGE-1] < $last_page_index-1 && $i == $display_range[PRODUCTS_PAGINATION_MID_RANGE-1]) $display_links_string .= '<li> ... </li>';

          }
        }
      
        $display_links_string .= $this->formatPageLink( PREVNEXT_TITLE_NEXT_PAGE, PP_TEXT_NEXT, $parameters . $this->page_name . '=' . ($this->current_page_number + 1), ($this->current_page_number == $this->number_of_pages) ? false : true, ' class="prevnext"');
        $display_links_string .= '</ul>';
      
        if (PRODUCTS_PAGINATION_DISPLAY_PAGEDROP == 'true' && $this->number_of_pages > 1) {  /*v1.4.3-c: Don't display if only 1 page */
          $display_links_string .= ppPageDropdown ($this->number_of_pages, $this->current_page_number, $this->formSuffix);
        }
      }
    
      if (PRODUCTS_PAGINATION_PRODUCT_COUNT == 'true') {  /*v1.4.3-c: Don't display count if only 1 page */
        $display_links_string .= ppCountDropdown ($this->number_of_rows, $this->number_of_rows_per_page, $this->formSuffix);
      }
    
      if ($display_links_string != '&nbsp;') {
        $display_links_string = '<div class="pagination">' . $display_links_string . '</div>';
      }
    }
    
    
//-eof-lat9 Products Pagination
    if ($display_links_string == '&nbsp;<strong class="current">1</strong>&nbsp;') {
      return '&nbsp;';
    } else {
      return $display_links_string;
    }
  }

  // display number of total products found
  function display_count($text_output) {
//-bof-lat9 Products Pagination - Create div around count if using products pagination
    $prefix = '';
    $suffix = '';
    if (PRODUCTS_PAGINATION_OTHER == 'true' && in_array($_GET['main_page'], explode(',', PRODUCTS_PAGINATION_OTHER_MAIN_PAGES))) {
      $prefix = '<div class="ppNextPrevCounter">';
      $suffix = '</div>';
    }
//-eof-lat9
    $to_num = ($this->number_of_rows_per_page * $this->current_page_number);
    if ($to_num > $this->number_of_rows) $to_num = $this->number_of_rows;

    $from_num = ($this->number_of_rows_per_page * ($this->current_page_number - 1));

    if ($to_num == 0) {
      $from_num = 0;
    } else {
      $from_num++;
    }

    if ($to_num <= 1) {
      // don't show count when 1
      return '';
    } else {
//-bof-lat9 Products Pagination - add prefix and suffix if enabled.
//      return sprintf($text_output, $from_num, $to_num, $this->number_of_rows);
      return $prefix . sprintf($text_output, $from_num, $to_num, $this->number_of_rows) . $suffix;
//-eof-lat9
    }
  }

//-bof-lat9 Products Pagination - Common formatting function
  private function formatPageLink($title, $name, $page_link_parms, $flag=true, $extra_class='') {
    global $request_type;
    if ($flag) {
      $returnValue = '<li><a href="' . zen_href_link($_GET['main_page'], $page_link_parms, $request_type, false) . '"' . $extra_class . ' title="' . htmlentities(zen_clean_html($title), ENT_COMPAT, CHARSET, true) . '">' . $name . '</a></li>';
    } else {
      $returnValue = '<li><span class="prevnext disablelink">' . $name . '</span></li>';
    }
    return $returnValue;
  }
//-eof-lat9
}
