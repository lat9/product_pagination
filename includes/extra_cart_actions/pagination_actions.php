<?php
// -----
// Part of the "Product Pagination" plugin by lat9 (lat9@vinosdefrutastropicales.com)
// Copyright (c) 2010-2024 Vinos de Frutas Tropicales
//

if (!defined('IS_ADMIN_FLAG')) {
    die ('Illegal Access');
}

// -----
// If there's a multiple-products-add-to-cart action to be acted upon and the plugin's handling has been configured to
// present a page- and/or pagecount-dropdown, need to provide some fixups for the follow-on processing.
//
if (isset ($_GET['action']) && $_GET['action'] == 'multiple_products_add_product' && (PRODUCTS_PAGINATION_DISPLAY_PAGEDROP == 'true' || PRODUCTS_PAGINATION_PRODUCT_COUNT == 'true') ) {
    if (isset ($_POST['pp_which_input'])) {
        $input_info = explode ('-', $_POST['pp_which_input']);
        if (count ($input_info) == 2) {
            switch ($input_info[0]) {
                case 'p':
                    $base_varname = 'page';
                    break;
                case 'pc':
                    $base_varname = 'pagecount';
                    break;
                default:
                    $base_varname = '';
                    break;
            }
            if ($base_varname != '') {
                $posted_varname = 'pp_' . $base_varname . $input_info[1];
                if (isset ($_POST[$posted_varname])) {
                    $_GET[$base_varname] = $_POST[$posted_varname];
                    unset ($_GET['action']);
                    zen_redirect (zen_href_link ($_GET['main_page'], zen_get_all_get_params ()));
                }
            }
        }
    }
}