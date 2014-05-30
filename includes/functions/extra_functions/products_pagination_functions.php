<?php
// ----- PRODUCTS_PAGINATION -----
// The following variables are preset by /includes/modules/YOUR_TEMPLATE/product_prev_next.php:
//
// $position ............... Array index of currently-displayed product
// $next_position .......... Array index of next product in the list
// $prev_position .......... Array index of previous product in the list
// $id_array ............... Array that contains the sorted product IDs
// $product_names_array .... Array that contains the sorted product names
// $products_found_count ... The base-1 count of all products for the category
// 
function products_next_prev_link ($offset, $name, $flag=true, $extra_class='') {
  global $page_link_parms, $id_array, $product_names_array;
  if ($flag) {
?>
      <li><a href="<?php echo zen_href_link(zen_get_info_page($id_array[$offset]), $page_link_parms . $id_array[$offset], 'NONSSL', false); ?>"<?php echo $extra_class; ?> title="<?php echo htmlentities(zen_clean_html($product_names_array[$offset]), ENT_COMPAT, CHARSET); ?>"><?php echo $name; ?></a></li>
<?php
  } else {
?>
      <li><span class="prevnext disablelink"><?php echo $name; ?></span></li>
<?php
  }
}

function ppPageDropdown ($lastPage, $whichPage, $formSuffix) {
  global $show_top_submit_button, $show_bottom_submit_button;
  $dropdown = '';
  if ($lastPage > 1) {
    $pageArray = array();
    for ($i=1; $i<=$lastPage; $i++) {
      $pageArray[] = array ( 'id' => $i, 'text' => $i );
    }
    //
    // If called from either an index product listing or the advanced search results page and "multiple products add to cart" is
    // enabled, there's already a <form> active so don't want to insert a form into a form.
    //
    $displayForm = !($show_top_submit_button || $show_bottom_submit_button);

    $baseValue = ($displayForm || $formSuffix == '1') ? zen_draw_hidden_field('prev_pagedrop', $whichPage) : '';
  
    $dropdown = '<div class="pp_page">' . (($displayForm) ? zen_draw_form('pp_page_form' . $formSuffix, zen_href_link($_GET['main_page'], zen_get_all_get_params()), 'post') : '') . PP_TEXT_PAGE . zen_draw_pull_down_menu ('pagedrop' . $formSuffix, $pageArray, $whichPage, 'onchange="this.form.submit();"') . $baseValue . (($displayForm) ? '</form>' : '') . '</div>';
  }
  return $dropdown;
}

function ppCountDropdown ($numItems, $whichCount, $formSuffix) {
  global $getoption_set, $get_option_variable, $cPath, $show_top_submit_button, $show_bottom_submit_button;  // Used on product-listing/index page
  $dropdown = '';
  $countArray = explode(',', PRODUCTS_PAGINATION_COUNT_VALUES);
  if (count($countArray) > 0) {
    $pageArray = array();
    for ($i=0, $n=sizeof($countArray), $done_all = false; $i<$n; $i++) {
      if ($countArray[$i] == '*') {
        if (!$done_all) {
          $pageArray[] = array( 'id' => 'all', 'text' => PP_TEXT_ALL );
          $done_all = true;
          
        }
      } elseif ($numItems > $countArray[$i]) {
        $pageArray[] = array( 'id' => $countArray[$i], 'text' => $countArray[$i]);
        
      } elseif (!$done_all) {
        $pageArray[] = array ('id' => 'all', 'text' => PP_TEXT_ALL);
        $done_all = true;
        
      }
    }

    //-----
    // Only display the dropdown if there's more than one item counts to display
    //    
    if (sizeof ($pageArray) > 1) {
      //
      // If called from either an index product listing or the advanced search results page and "multiple products add to cart" is
      // enabled, there's already a <form> active so don't want to insert a form into a form.
      //
      $displayForm = !($show_top_submit_button || $show_bottom_submit_button);
      $formPage  = ($_GET['main_page'] == FILENAME_ADVANCED_SEARCH_RESULT) ? FILENAME_ADVANCED_SEARCH : $_GET['main_page']; /*v1.4.2-c-lat9*/
      
      $whichCount = (isset($_GET['pagecount']) && $_GET['pagecount'] == 'all') ? 'all' : $whichCount;
      
      $baseValue = (($displayForm || $formSuffix == '1') && $whichCount != '') ? zen_draw_hidden_field('prev_pagecount', $whichCount) : '';
      
      $dropdown  = '<div class="pp_count">' . (($displayForm) ? zen_draw_form('pp_count_form' . $formSuffix, zen_href_link($_GET['main_page'], zen_get_all_get_params()), 'post') : '') . zen_hide_session_id() . zen_draw_hidden_field ('main_page', $_GET['main_page']) . $baseValue;
    
      $hiddenVar = ppHiddenVarsList(); /*v1.4.2-c-lat9: Moved to separate function */
    
      if ($_GET['main_page'] == FILENAME_DEFAULT) {
        if (!$getoption_set) {
          $dropdown .= zen_draw_hidden_field('cPath', $cPath);
        } else {
          // draw manufacturers_id
          $dropdown .= zen_draw_hidden_field($get_option_variable, $_GET[$get_option_variable]);
        }
        if ($get_option_variable != 'manufacturers_id' && isset($_GET['manufacturers_id']) && $_GET['manufacturers_id'] > 0) {
          $hiddenVar[] = 'manufacturers_id';
        }
      }

      $dropdown .= ppCreateHiddenInputs ($hiddenVar);  /*v1.4.2-c-lat9: Moved to separate function */
      $dropdown .= PP_TEXT_ITEMS_PER_PAGE . zen_draw_pull_down_menu ('pagecount' . $formSuffix, $pageArray, $whichCount, 'onchange="this.form.submit();"') .  (($displayForm) ? '</form>' : '') . '</div>';
      
    }
  }
  return $dropdown;
}

function ppHiddenVarsList() {
  return explode (',', 'disp_order,sort,filter_id,music_genre_id,record_company_id,typefilter,keyword,search_in_description,categories_id,inc_subcat,manufacturers_id,pfrom,pto,dfrom,dto,alpha_filter_id');
}

function ppCreateHiddenInputs ($hiddenVar) {
  $inputVars = '';
  foreach ($hiddenVar as $varName) {
    $inputVars .= (isset($_GET[$varName]) ? zen_draw_hidden_field ($varName, $_GET[$varName]) : '');
  }  
  return $inputVars;
}