<?php
// -----
// Part of the "Product Pagination" plugin by lat9 (lat9@vinosdefrutastropicales.com)
// Copyright (c) 2010-2024 Vinos de Frutas Tropicales
//

/**
 * Page Template
 *
 * Loaded by main_page=index
 * Displays product-listing when a particular category/subcategory is selected for browsing
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 Jan 27 Modified in v2.0.0-alpha1 $
 */

?>
<div class="centerColumn" id="indexProductList">

    <div id="cat-top" class="group">
        <div id="cat-left" class="back">
            <h1 id="productListHeading"><?php
                echo $current_categories_name; ?></h1>

            <?php
            if (PRODUCT_LIST_CATEGORIES_IMAGE_STATUS === 'true') {
                // categories_image
                if ($categories_image = zen_get_categories_image($current_category_id)) {
            ?>
                    <div id="categoryImgListing" class="categoryImg"><?php
                        echo zen_image(DIR_WS_IMAGES . $categories_image, '', CATEGORY_ICON_IMAGE_WIDTH, CATEGORY_ICON_IMAGE_HEIGHT); ?></div>
                    <?php
                }
            } // categories_image_status
            ?>
        </div>

        <?php
        // categories_description
        if ($current_categories_description !== '') {
            ?>
            <div id="indexProductListCatDescription" class="content"><?php
                echo $current_categories_description; ?></div>
        <?php
        } // categories_description ?>
    </div>

<?php
    if (!empty($listing)) { ?>
    <div id="filter-wrapper" class="group">
<?php } ?>

<?php
        $check_for_alpha = $listing_sql;
        $check_for_alpha = $db->Execute($check_for_alpha);

        if ($do_filter_list || isset($_GET['alpha_filter_id']) || ($check_for_alpha->RecordCount() > 0 && PRODUCT_LIST_ALPHA_SORTER === 'true')) {
            $form = zen_draw_form('filter', zen_href_link(FILENAME_DEFAULT), 'get') . '<label class="inputLabel">' . TEXT_SHOW . '</label>';

            echo $form;
            echo zen_draw_hidden_field('main_page', FILENAME_DEFAULT);

            // draw cPath if known
            if (empty($getoption_set)) {
                echo zen_draw_hidden_field('cPath', $cPath);
            } else {
                // draw manufacturers_id
                echo zen_draw_hidden_field($get_option_variable, $_GET[$get_option_variable]);
            }

            // draw music_genre_id
            if (isset($_GET['music_genre_id']) && $_GET['music_genre_id'] > 0) {
                echo zen_draw_hidden_field('music_genre_id', $_GET['music_genre_id']);
            }

            // draw record_company_id
            if (isset($_GET['record_company_id']) && $_GET['record_company_id'] > 0) {
                echo zen_draw_hidden_field('record_company_id', $_GET['record_company_id']);
            }

            // draw typefilter
            if (isset($_GET['typefilter']) && $_GET['typefilter'] > 0) {
                echo zen_draw_hidden_field('typefilter', $_GET['typefilter']);
            }

            // draw manufacturers_id if not already done earlier
            if (!(isset($get_option_variable) && $get_option_variable === 'manufacturers_id') && !empty($_GET['manufacturers_id'])) {
                echo zen_draw_hidden_field('manufacturers_id', $_GET['manufacturers_id']);
            }

            // draw disp_order
            if (!empty($_GET['disp_order'])) {
                echo zen_draw_hidden_field('disp_order', $_GET['disp_order']);
            }

            // draw sort
            if (!empty($_GET['sort'])) {
                echo zen_draw_hidden_field('sort', $_GET['sort']);
            }

//-bof-products_pagination-lat9  *** 1 of 1 ***
  if (isset($_GET['pagecount']) && zen_not_null($_GET['pagecount'])) echo zen_draw_hidden_field('pagecount', $_GET['pagecount']);
//-eof-products_pagination-lat9  *** 1 of 1 ***
            // draw filter_id (ie: category/mfg depending on $options)
            if ($do_filter_list) {
                echo zen_draw_pull_down_menu('filter_id', $options, ($_GET['filter_id'] ?? ''), 'onchange="this.form.submit()"');
            }


            // draw alpha sorter
            require(DIR_WS_MODULES . zen_get_module_directory(FILENAME_PRODUCT_LISTING_ALPHA_SORTER));

            echo '</form>';
        }
?>

<?php
        /**
         * display the product sort dropdown
         */
        require($template->get_template_dir('/tpl_modules_listing_display_order.php', DIR_WS_TEMPLATE, $current_page_base, 'templates') . '/tpl_modules_listing_display_order.php');
?>

<?php
        // end wrapper
?>
<?php
        if (!empty($listing)) {
?>
    </div>
<?php
        }
?>


<?php
/**
 * require the code for listing products
 */
require($template->get_template_dir('tpl_modules_product_listing.php', DIR_WS_TEMPLATE, $current_page_base, 'templates') . '/' . 'tpl_modules_product_listing.php');
?>


<?php
    //// bof: categories error
    if ($error_categories) {
        // verify lost category and reset category
        $check_category = $db->Execute("SELECT categories_id FROM " . TABLE_CATEGORIES . " WHERE categories_id='" . $cPath . "'");
        if ($check_category->RecordCount() === 0) {
            $new_products_category_id = '0';
            $cPath = '';
        }

        $show_display_category = $db->Execute(SQL_SHOW_PRODUCT_INFO_MISSING);
        foreach ($show_display_category as $content_box_to_display) {

            if ($content_box_to_display['configuration_key'] === 'SHOW_PRODUCT_INFO_MISSING_FEATURED_PRODUCTS') { ?>
                <?php
                /**
                 * display the Featured Products Center Box
                 */
                ?>
                <?php
                require($template->get_template_dir('tpl_modules_featured_products.php', DIR_WS_TEMPLATE, $current_page_base, 'templates') . '/tpl_modules_featured_products.php');
                ?>
            <?php
            }
            ?>

            <?php
            if ($content_box_to_display['configuration_key'] === 'SHOW_PRODUCT_INFO_MISSING_SPECIALS_PRODUCTS') { ?>
                <?php
                /**
                 * display the Special Products Center Box
                 */
                ?>
                <?php
                require($template->get_template_dir('tpl_modules_specials_default.php', DIR_WS_TEMPLATE, $current_page_base, 'templates') . '/tpl_modules_specials_default.php');
                ?>
            <?php
            }
            ?>

            <?php
            if ($content_box_to_display['configuration_key'] === 'SHOW_PRODUCT_INFO_MISSING_NEW_PRODUCTS') { ?>
                <?php
                /**
                 * display the New Products Center Box
                 */
                ?>
                <?php
                require($template->get_template_dir('tpl_modules_whats_new.php', DIR_WS_TEMPLATE, $current_page_base, 'templates') . '/tpl_modules_whats_new.php');
                ?>
            <?php
            }
            ?>

            <?php
            if ($content_box_to_display['configuration_key'] === 'SHOW_PRODUCT_INFO_MISSING_UPCOMING') {
                include DIR_WS_MODULES . zen_get_module_directory(FILENAME_UPCOMING_PRODUCTS);
            }
            ?>
        <?php
        } // foreach
        ?>
<?php
    } //// eof: categories error
?>

    <?php
    //// bof: categories
    $show_display_category = $db->Execute(SQL_SHOW_PRODUCT_INFO_LISTING_BELOW);
    if ($error_categories === false && $show_display_category->RecordCount() > 0) {
    ?>

        <?php
        $show_display_category = $db->Execute(SQL_SHOW_PRODUCT_INFO_LISTING_BELOW);
        foreach ($show_display_category as $content_box_to_display) {

            if ($content_box_to_display['configuration_key'] === 'SHOW_PRODUCT_INFO_LISTING_BELOW_FEATURED_PRODUCTS') { ?>
                <?php
                /**
                 * display the Featured Products Center Box
                 */
                ?>
                <?php
                require($template->get_template_dir('tpl_modules_featured_products.php', DIR_WS_TEMPLATE, $current_page_base, 'templates') . '/tpl_modules_featured_products.php');
                ?>
            <?php
            }
            ?>

            <?php
            if ($content_box_to_display['configuration_key'] === 'SHOW_PRODUCT_INFO_LISTING_BELOW_SPECIALS_PRODUCTS') { ?>
                <?php
                /**
                 * display the Special Products Center Box
                 */
                ?>
                <?php
                require($template->get_template_dir('tpl_modules_specials_default.php', DIR_WS_TEMPLATE, $current_page_base, 'templates') . '/tpl_modules_specials_default.php');
                ?>
            <?php
            }
            ?>

            <?php
            if ($content_box_to_display['configuration_key'] === 'SHOW_PRODUCT_INFO_LISTING_BELOW_NEW_PRODUCTS') { ?>
                <?php
                /**
                 * display the New Products Center Box
                 */
                ?>
                <?php
                require($template->get_template_dir('tpl_modules_whats_new.php', DIR_WS_TEMPLATE, $current_page_base, 'templates') . '/tpl_modules_whats_new.php');
                ?>
            <?php
            }
            ?>

            <?php
            if ($content_box_to_display['configuration_key'] === 'SHOW_PRODUCT_INFO_LISTING_BELOW_UPCOMING') {
                include(DIR_WS_MODULES . zen_get_module_directory(FILENAME_UPCOMING_PRODUCTS));
            }
            ?>
            <?php
        } // foreach
    ?>

    <?php
    } //// eof: categories
    ?>

</div>
