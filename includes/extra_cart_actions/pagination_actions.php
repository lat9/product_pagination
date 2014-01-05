<?php
/**
 * @copyright Copyright 2003-2011 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: pagination_actions.php 2012-11-21 lat9 $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
// -----
// If there's a multiple-products-add-to-cart action to be acted upon and neither the top nor bottom add-selected-items-to-cart submit
// buttons were clicked (i.e. they're both empty), then one of the Product Pagination 'onchange' counts were selected.  'Negate' the add-to-cart
// request so that the product page or count dropdown have been used, they'll be acted upon by the split_page_results.php class-file.
//
$zco_notifier->notify('PAGINATION_ACTIONS', array('get' => $_GET, 'post' => $_POST));
if (isset($_GET['action']) && $_GET['action'] == 'multiple_products_add_product' && (PRODUCTS_PAGINATION_DISPLAY_PAGEDROP == 'true' || PRODUCTS_PAGINATION_PRODUCT_COUNT == 'true') ) {
  if (isset($_POST) && !isset($_POST['submit1_x']) && !isset($_POST['submit1_y']) && !isset($_POST['submit2_x']) && !isset($_POST['submit2_y'])) {
    unset($_GET['action']);
    unset($_REQUEST['action']);

  }
}