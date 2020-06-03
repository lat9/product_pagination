<?php
// -----
// Part of the "Products Pagination" plugin by lat9 (lat9@vinosdefrutastropicales.com)
// Copyright (c) 2016 Vinos de Frutas Tropicales
//
if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

class products_pagination_observer extends base 
{
    public function __construct() 
    {
        $this->id_array = array ();
        $this->product_names_array = array ();
        
        $this->attach ($this, array ( /* From /includes/init_includes/init_canonical.php */ 'NOTIFY_INIT_CANONICAL_PARAM_WHITELIST' ));
        
        if (!class_exists ('Mobile_Detect')) {
            include_once (DIR_WS_CLASSES . 'Mobile_Detect.php');
        }
        $detect = new Mobile_Detect ();
        $this->isTablet = $detect->isTablet () || (isset ($_SESSION['layoutType']) && $_SESSION['layoutType'] == 'tablet');
        $this->isMobile = (!$detect->isTablet () && $detect->isMobile ()) || (isset ($_SESSION['layoutType']) && $_SESSION['layoutType'] == 'mobile');
        $this->isDesktop = !($this->isTablet || $this->isMobile);
        
        $this->isEnabled = (defined ('PRODUCTS_PAGINATION_ENABLE') && PRODUCTS_PAGINATION_ENABLE == 'true');
        $this->isEnabledMobile = $this->isEnabled && (defined ('PRODUCTS_PAGINATION_ENABLE_MOBILE') && PRODUCTS_PAGINATION_ENABLE_MOBILE == 'true');
    }
  
    public function update (&$class, $eventID, $p1, &$p2, &$p3, &$p4, &$p5) 
    {
        switch ($eventID) {
            // -----
            // Add the pagecount variable to the canonical links' $excludeParams array.
            //
            case 'NOTIFY_INIT_CANONICAL_PARAM_WHITELIST':
                $p2[] = 'pagecount';
                break;

            default:
                break;
        }   
    }
    
    // -----
    // This function returns a boolean value indicating whether or not the plugin is to be used on the current page.
    //
    public function isPaginationEnabled ($whichType)
    {
        $enabled = ($this->isEnabled && $this->isDesktop) || ($this->isEnabledMobile && !$this->isDesktop);
        if ($enabled && $whichType == 'other' &&
            !( PRODUCTS_PAGINATION_OTHER == 'true' && PRODUCTS_PAGINATION_OTHER_MAIN_PAGES != '' && isset ($_GET['main_page']) &&
               in_array ($_GET['main_page'], explode (',', PRODUCTS_PAGINATION_OTHER_MAIN_PAGES)) ) ) {
            $enabled = false;
        }
        $this->pagePaginationEnabled = $enabled;
        return $enabled;
    }
    
    // -----
    // This function, called by the plugin's /includes/modules/pp_product_prev_next.php, initializes the information needed to
    // display the products' next/prev links.
    //
    public function initializeNextPrev ()
    {
        global $db, $cPath, $cPath_array, $current_category_id;
        if (PRODUCT_INFO_PREVIOUS_NEXT != 0) {
            switch (PRODUCT_INFO_PREVIOUS_NEXT_SORT) {
                case (0):
                    $prev_next_order= ' order by LPAD(p.products_id,11,"0")';
                    break;
                case (1):
                    $prev_next_order= " order by pd.products_name";
                    break;
                case (2):
                    $prev_next_order= " order by p.products_model";
                    break;
                case (3):
                    $prev_next_order= " order by p.products_price_sorter, pd.products_name";
                    break;
                case (4):
                    $prev_next_order= " order by p.products_price_sorter, p.products_model";
                    break;
                case (5):
                    $prev_next_order= " order by pd.products_name, p.products_model";
                    break;
                case (6):
                    $prev_next_order= ' order by LPAD(p.products_sort_order,11,"0"), pd.products_name';
                    break;
                default:
                    $prev_next_order= " order by pd.products_name";
                    break;
            }

            if (!zen_not_null ($cPath) && isset ($_GET['products_id'])) {
                $cPath = zen_get_product_path ((int)$_GET['products_id']);
                $cPath_array = zen_parse_category_path ($cPath);
                $cPath = implode ('_', $cPath_array);
                $current_category_id = $cPath_array[(count ($cPath_array)-1)];
            }
            $this->page_link_parms = "cPath=$cPath&products_id=";
            $this->cPath = $cPath;

            $sql = "SELECT p.products_id, p.products_model, p.products_price_sorter, pd.products_name, p.products_sort_order
                      FROM " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_PRODUCTS_TO_CATEGORIES . " ptc
                     WHERE p.products_status = 1 
                       AND p.products_id = pd.products_id 
                       AND pd.language_id = " . (int)$_SESSION['languages_id'] . " 
                       AND p.products_id = ptc.products_id 
                       AND ptc.categories_id = " . (int)$current_category_id . $prev_next_order;
            $products_ids = $db->Execute ($sql);
            $this->products_found_count = $products_ids->RecordCount();

            while (!$products_ids->EOF) {
                $this->id_array[] = $products_ids->fields['products_id'];
                $this->product_names_array[] = $products_ids->fields['products_name'];
                $products_ids->MoveNext();
            }

            if (count ($this->id_array) != 0) {
                $this->counter = 0;
                foreach ($this->id_array as $key => $value) {
                    if ($value == (int)$_GET['products_id']) {
                        $this->position = $this->counter;
                        if ($key == 0) {
                            $this->previous_position = -1;
                        } else {
                            $this->previous_position = $key - 1;
                        }
                        if (isset ($this->id_array[$key + 1]) && $this->id_array[$key + 1]) {
                            $this->next_position = $key + 1;
                        } else {
                            $this->next_position = 0;
                        }
                    }
                    $last = $value;
                    $this->counter++;
                }

                $sql = "SELECT categories_name
                          FROM " . TABLE_CATEGORIES_DESCRIPTION . "
                         WHERE categories_id = " . (int)$current_category_id . " 
                           AND language_id = " . (int)$_SESSION['languages_id'] . " LIMIT 1";

                $category_name_row = $db->Execute ($sql);
                $this->category_name = $category_name_row->fields['categories_name'];
            }
        }
    }
    
    // -----
    // This function, called by the plugin's /includes/templates/template_default/tpl_pp_products_next_previous.php, formats the
    // products' prev/next links for output.
    //
    public function formatNextPrev()
    {
        $return_html = '';
        $products_found_count = count ($this->id_array);
        if ($products_found_count > 1) {
            $products_last_index = $products_found_count - 1;

            $display_prev_link  = ($this->position == 0) ? false : true;
            $display_next_link  = ($this->position == $products_last_index) ? false : true;
            
            $return_html  = '<div class="ppNextPrevCounter">' . PHP_EOL;
            $return_html .= '  <p' . ((PRODUCTS_PAGINATION_LISTING_LINK == 'true') ? ' class="back pagination-list"' : '') . '>' . PP_PREV_NEXT_PRODUCT . ($this->position+1) . PP_PREV_NEXT_PRODUCT_SEP . $this->counter . '</p>' . PHP_EOL;

            if (PRODUCTS_PAGINATION_LISTING_LINK == 'true') {
                $return_html .= '  <div class="prod-pagination prevnextReturn">' . PHP_EOL;
                $return_html .= '    <ul>' . PHP_EOL;
                $return_html .= '      <li><a href="' . zen_href_link (FILENAME_DEFAULT, 'cPath=' . $this->cPath) . '" class="prevnext" title="' . sprintf (PP_TEXT_PRODUCT_LISTING_TITLE, $this->category_name) . '">' . PP_TEXT_PRODUCT_LISTING . '</a></li>' . PHP_EOL;
                $return_html .= '    </ul>' . PHP_EOL;
                $return_html .= '  </div>' . PHP_EOL;
            }
            
            $return_html .= '<div class="clearBoth"></div></div>' . PHP_EOL;  //-END ppNextPrevCounter
            
            $return_html .= '<div class="prod-pagination pagination-links">' . PHP_EOL;
            $return_html .= '  <ul>' . PHP_EOL;

            $return_html .= '    ' . $this->createNextPrevLink ($this->previous_position, PP_TEXT_PREVIOUS, $display_prev_link, ' class="prevnext"') . PHP_EOL;

            if ($products_found_count <= PRODUCTS_PAGINATION_MAX) {
                for ($i=0; $i < $products_found_count; $i++) {
                    $return_html .= $this->createNextPrevLink ($i, $i+1, true, ($i === $this->position ? ' class="currentpage"' : '') . $this->page_link_parms) . PHP_EOL;
                }
            } else {
                $first_product_link = $this->position - floor (PRODUCTS_PAGINATION_MID_RANGE/2);
                $last_product_link  = $this->position + floor (PRODUCTS_PAGINATION_MID_RANGE/2);

                if ($first_product_link < 0) {
                    $last_product_link += abs ($first_product_link);
                    $first_product_link = 0;
                }
                if ($last_product_link > $products_last_index) {
                    $first_product_link -= $last_product_link - $products_last_index;
                    $last_product_link   = $products_last_index;
                }
                $display_range = range ($first_product_link, $last_product_link);

                for ($i=0; $i < $products_found_count; $i++) {
                    if ($display_range[0] > 1 && $i == $display_range[0]) {
                        $return_html .= '    <li class="hellip"> ... </li>' . PHP_EOL;
                    }
                    // loop through all pages. if first, last, or in range, display
                    if ($i == 0 || $i == $products_last_index || in_array ($i, $display_range)) {
                        $return_html .= '    ' . $this->createNextPrevLink ($i, $i+1, true, ($i == $this->position) ? ' class="currentpage"' : '') . PHP_EOL;
                    }
                    if ($display_range[PRODUCTS_PAGINATION_MID_RANGE-1] < $products_last_index-1 && $i == $display_range[PRODUCTS_PAGINATION_MID_RANGE-1]) {
                        $return_html .= '    <li class="hellip"> ... </li>' . PHP_EOL;
                    }
                }
            } 
            $return_html .= $this->createNextPrevLink ($this->next_position, PP_TEXT_NEXT, $display_next_link, ' class="prevnext"') . PHP_EOL;
            $return_html .= '  </ul>' . PHP_EOL;
            $return_html .= '</div>' . PHP_EOL;  //-END pagination-links
 
            $return_html = '<div class="ppNextPrevWrapper">' . $return_html . '<div class="clearBoth"></div></div>';
        }
        return $return_html;
    }
    
    private function createNextPrevLink ($offset, $name, $display_flag=true, $extra_class='')
    {
        if ($display_flag) {
            $return_html = '<li><a href="' . zen_href_link(zen_get_info_page($this->id_array[$offset]), $this->page_link_parms . $this->id_array[$offset], 'NONSSL', false) . '"' . $extra_class . ' title="' . htmlentities(zen_clean_html($this->product_names_array[$offset]), ENT_COMPAT, CHARSET) . '">' . $name . '</a></li>';
        } else {
            $return_html = '<li><span class="prevnext disablelink">' . $name . '</span></li>';
        }
        return $return_html;
    }
}