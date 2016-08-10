<?php
// -----
// Part of the "Product Pagination" plugin by lat9 (lat9@vinosdefrutastropicales.com)
// Copyright (c) 2010-2016 Vinos de Frutas Tropicales
// 
if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

// -----
// Note:  This module is loaded by the "base" product_prev_next.php module, if the "Product Pagination" plugin is installed and enabled.
// It's based on the module's contents as distributed in Zen Cart 1.5.5a.
//
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

    if ($cPath < 1) {
        $cPath = zen_get_product_path ((int)$_GET['products_id']);
        $cPath_array = zen_parse_category_path($cPath);
        $cPath = implode ('_', $cPath_array);
        $current_category_id = $cPath_array[(sizeof($cPath_array)-1)];
    }


    $sql = "SELECT p.products_id, p.products_model, p.products_price_sorter, pd.products_name, p.products_sort_order
              FROM " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_PRODUCTS_TO_CATEGORIES . " ptc
             WHERE p.products_status = 1 
               AND p.products_id = pd.products_id 
               AND pd.language_id = " . (int)$_SESSION['languages_id'] . " 
               AND p.products_id = ptc.products_id 
               AND ptc.categories_id = " . (int)$current_category_id . $prev_next_order;
    $products_ids = $db->Execute ($sql);
    $products_found_count = $products_ids->RecordCount();

    $id_array = array ();
    $product_names_array = array ();
    while (!$products_ids->EOF) {
        $id_array[] = $products_ids->fields['products_id'];
        $product_names_array[] = $products_ids->fields['products_name'];
        $products_ids->MoveNext();
    }

    if (count ($id_array) != 0) {
        $counter = 0;
        foreach ($id_array as $key => $value) {
            if ($value == (int)$_GET['products_id']) {
                $position = $counter;
                if ($key == 0) {
                    $previous_position = -1;
                    $previous = -1;
                } else {
                    $previous_position = $key - 1;
                    $previous = $id_array[$previous_position];
                }
                if (isset($id_array[$key + 1]) && $id_array[$key + 1]) {
                    $next_position = $key + 1;
                    $next_item = $id_array[$key + 1];
                } else {
                    $next_position = 0;
                    $next_item = $id_array[0];
                }
            }
            $last = $value;
            $counter++;
        }

        $sql = "SELECT categories_name
                  FROM " . TABLE_CATEGORIES_DESCRIPTION . "
                 WHERE categories_id = " . (int)$current_category_id . " 
                   AND language_id = " . (int)$_SESSION['languages_id'] . " LIMIT 1";

        $category_name_row = $db->Execute ($sql);
    }
}
