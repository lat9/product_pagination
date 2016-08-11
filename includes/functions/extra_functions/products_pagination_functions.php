<?php

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

// -----
// This function inspects the plugin's configuration and returns a boolean indication as to whether the current page
// qualifies as an "other" page being handled by "Product Pagination".
//
function ppOtherSplitPageActive ()
{
    $is_active = false;
    if (defined ('PRODUCTS_PAGINATION_ENABLE') && PRODUCTS_PAGINATION_ENABLE == 'true') {
        if (PRODUCTS_PAGINATION_OTHER == 'true' && zen_not_null (PRODUCTS_PAGINATION_OTHER_MAIN_PAGES) && isset ($_GET['main_page'])) {
            $is_active = in_array ($_GET['main_page'], explode (',', PRODUCTS_PAGINATION_OTHER_MAIN_PAGES));
        }
    }
    return $is_active;
}