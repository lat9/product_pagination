<?php
// -----
// Part of the "Product Pagination" plugin by lat9 (lat9@vinosdefrutastropicales.com)
// Copyright (c) 2010-2016 Vinos de Frutas Tropicales
// 
if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

if (PRODUCTS_PAGINATION_OTHER == 'true' && isset ($_GET['main_page']) && in_array ($_GET['main_page'], explode(',', PRODUCTS_PAGINATION_OTHER_MAIN_PAGES))) {
    if (zen_not_null ($_POST['pagedrop1']) && $_POST['pagedrop1'] != $_POST['prev_pagedrop']) {
        $_POST['pagedrop'] = $_POST['pagedrop1'];
    } elseif (zen_not_null ($_POST['pagedrop2']) && $_POST['pagedrop2'] != $_POST['prev_pagedrop']) {
        $_POST['pagedrop'] = $_POST['pagedrop2'];
    }
    
    if (zen_not_null ($_POST['pagecount1']) && $_POST['pagecount1'] != $_POST['prev_pagecount']) {
        $_GET['pagecount'] = $_POST['pagecount1'];
    } elseif (zen_not_null ($_POST['pagecount2']) && $_POST['pagecount2'] != $_POST['prev_pagecount']) {
        $_GET['pagecount'] = $_POST['pagecount2'];  
    } elseif (zen_not_null($_POST['prev_pagecount'])) {
        $_GET['pagecount'] = $_POST['prev_pagecount'];
    }
    
    if (zen_not_null ($_POST['pagedrop'])) {
        $_GET['page'] = $_POST['pagedrop'];
    }
}