<?php
/**
 * @package initSystem
 * @copyright Copyright 2003-2011 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: init_pagination.php 2012-11-20 lat9 $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}

if (PRODUCTS_PAGINATION_OTHER == 'true' && in_array($_GET['main_page'], explode(',', PRODUCTS_PAGINATION_OTHER_MAIN_PAGES))) {
  if (isset($_POST)) {
    if (zen_not_null($_POST['pagedrop1']) && $_POST['pagedrop1'] != $_POST['prev_pagedrop']) {
      $_POST['pagedrop'] = $_POST['pagedrop1'];
      
    } elseif (zen_not_null($_POST['pagedrop2']) && $_POST['pagedrop2'] != $_POST['prev_pagedrop']) {
      $_POST['pagedrop'] = $_POST['pagedrop2'];
      
    }
    
    if (zen_not_null($_POST['pagecount1']) && $_POST['pagecount1'] != $_POST['prev_pagecount']) {
      $_GET['pagecount'] = $_POST['pagecount1'];
      
    } elseif (zen_not_null($_POST['pagecount2']) && $_POST['pagecount2'] != $_POST['prev_pagecount']) {
      $_GET['pagecount'] = $_POST['pagecount2'];
      
    } elseif (zen_not_null($_POST['prev_pagecount'])) {
      $_GET['pagecount'] = $_POST['prev_pagecount'];
    }
    
    if (zen_not_null($_POST['pagedrop'])) $_GET['page'] = $_POST['pagedrop'];
  }

}