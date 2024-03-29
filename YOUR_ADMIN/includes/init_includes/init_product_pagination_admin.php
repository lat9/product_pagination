<?php
// -----
// Part of the "Products Pagination" plugin by lat9 (lat9@vinosdefrutastropicales.com)
//
// Starting with v2.0.0 of the plugin, perform the auto-install of the various configuration items.
//
// Last updated: v3.0.0
//
define('PRODUCTS_PAGINATION_VERSION_CURRENT', '3.0.0-beta1');
define('PRODUCTS_PAGINATION_VERSION_CURRENT_DATE', '03-24-2024');

$pp_current_version = PRODUCTS_PAGINATION_VERSION_CURRENT . ' (' . PRODUCTS_PAGINATION_VERSION_CURRENT_DATE . ')';

// -----
// Quick return if an admin's not yet logged in or there's no update to be performed.
//
if (!isset($_SESSION['admin_id']) || (defined('PRODUCTS_PAGINATION_VERSION') && PRODUCTS_PAGINATION_VERSION === $pp_current_version)) {
    return;
}

$configurationGroupTitle = 'Products Pagination';
$configuration = $db->Execute("SELECT configuration_group_id FROM " . TABLE_CONFIGURATION_GROUP . " WHERE configuration_group_title = '$configurationGroupTitle' LIMIT 1");
if (!$configuration->EOF) {
    $cgi = $configuration->fields['configuration_group_id'];
} else {
    $db->Execute(
        "INSERT INTO " . TABLE_CONFIGURATION_GROUP . "
            (configuration_group_title, configuration_group_description, sort_order, visible)
         VALUES
            ('$configurationGroupTitle', 'Product Pagination Settings', 1, 1)"
    );
    $cgi = $db->Insert_ID(); 
    $db->Execute("UPDATE " . TABLE_CONFIGURATION_GROUP . " SET sort_order = $cgi WHERE configuration_group_id = $cgi LIMIT 1");
}

// -----
// Set the various configuration items, if "Product Pagination" wasn't previously installed.
//
if (!defined('PRODUCTS_PAGINATION_MAX')) {
    $db->Execute(
        "INSERT INTO " . TABLE_CONFIGURATION . "
            (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function, set_function)
         VALUES
            ('Products Pagination Version', 'PRODUCTS_PAGINATION_VERSION', '0.0.0', 'This is the current version of the plugin.<br>', $cgi, 1, now(), NULL, 'zen_cfg_read_only('),

            ('Enable Products Pagination?', 'PRODUCTS_PAGINATION_ENABLE', 'false', 'Use this setting to enable (default) or disable the plugin\'s overall operation.<br><br><b>Default: false</b>', $cgi, 5, now(), NULL, 'zen_cfg_select_option([\'true\', \'false\'],'),

            ('Enable Products Pagination (Mobile)?', 'PRODUCTS_PAGINATION_ENABLE_MOBILE', 'false', 'Use this setting to enable or disable (default) the pagination display on <em>mobile</em> devices &mdash; <em>assuming</em> that your template provides support for mobile devices (like the <code>responsive_classic</code> template that is built into Zen Cart 1.5.5a)!<br><br><b>Default: false</b>', $cgi, 6, now(), NULL, 'zen_cfg_select_option([\'true\', \'false\'],'),

            ('Products Pagination &mdash; Maximum Links', 'PRODUCTS_PAGINATION_MAX', '10', 'This is the maximum number of product links to be displayed before pagination begins.  This value should be greater than the number of <em>Intermediate Links</em>.<br><br><b>Default: 10</b><br>', $cgi, 10, now(), NULL, NULL),

            ('Products Pagination &mdash; Intermediate Links', 'PRODUCTS_PAGINATION_MID_RANGE', '7', 'This is the number of intermediate links to be shown when the number of products in the current category is greater than the <em>Maximum Links</em>; the first and last product link is always shown.  The value should be an odd number for link symmetry.<br><br><b>Default: 7</b><br>', $cgi, 20, now(), NULL, NULL),

            ('Enable product listing link?', 'PRODUCTS_PAGINATION_LISTING_LINK', 'true', 'If enabled, a &quot;View Product listing&quot; link is shown on the same line as &quot;Viewing product x of y&quot;.<br><br><b>Default: true</b>', $cgi, 30, now(), NULL, 'zen_cfg_select_option([\'true\', \'false\'],'),

            ('Enable links on other pages?', 'PRODUCTS_PAGINATION_OTHER', 'true', 'If enabled, the &quot;Other pages to link&quot; will have the pagination links applied.<br><br><b>Default: true</b>', $cgi, 40, now(), NULL, 'zen_cfg_select_option([\'true\', \'false\'],'),

            ('Other pages to link', 'PRODUCTS_PAGINATION_OTHER_MAIN_PAGES', 'account_history, advanced_search_result, featured_products,index, product_reviews, products_all, products_new, reviews, specials', 'This comma-separated list identifies the &quot;other&quot; pages to which the pagination display should be applied.', $cgi, 50, now(), NULL, NULL),

            ('Include page-select drop-down?', 'PRODUCTS_PAGINATION_DISPLAY_PAGEDROP', 'false', 'If enabled, a drop-down menu is displayed on the <strong>other</strong> pages to allow the customer to go to a specific page number.<br><b>Default: false</b>', $cgi, 60, now(), NULL, 'zen_cfg_select_option([\'true\', \'false\'],'),

            ('Include item-count drop-down?', 'PRODUCTS_PAGINATION_PRODUCT_COUNT', 'false', 'If enabled, a drop-down menu is displayed to allow the customer to choose the number of items displayed for the <strong>other</strong> pages.  The count choices are contained in &quot;Item Counts&quot; (see below).<br><b>Default: false</b>', $cgi, 70, now(), NULL, 'zen_cfg_select_option([\'true\', \'false\'],'),

            ('Item counts', 'PRODUCTS_PAGINATION_COUNT_VALUES', '10,25,50,100,*', 'This comma-separated list identifies the item-count choices that will be displayed in a drop-down menu to the customer.  The value \'*\' corresponds to <em>All</em> the items being displayed.', $cgi, 80, now(), NULL, NULL)"
    );

    define('PRODUCTS_PAGINATION_VERSION', '0.0.0');
// -----
// If a previous, i.e. pre-v2.0.0, installation is found, add the two new configuration items and update the configuration values' sort-orders so
// that the updated configuration displays in the right order.
//
} elseif (!defined('PRODUCTS_PAGINATION_VERSION')) {
    $db->Execute(
        "INSERT INTO " . TABLE_CONFIGURATION . "
            (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, use_function, set_function )
         VALUES
            ('Products Pagination Version', 'PRODUCTS_PAGINATION_VERSION', '0.0.0', 'This is the current version of the plugin.<br>', $cgi, 1, now(), NULL, 'zen_cfg_read_only('),

            ( 'Enable Products Pagination?', 'PRODUCTS_PAGINATION_ENABLE', 'false', 'Use this setting to enable (default) or disable the plugin\'s overall operation.<br><br><b>Default: false</b>', $cgi, 5, now(), NULL, 'zen_cfg_select_option([\'true\', \'false\'],'),

            ( 'Enable Products Pagination (Mobile)?', 'PRODUCTS_PAGINATION_ENABLE_MOBILE', 'false', 'Use this setting to enable or disable (default) the pagination display on <em>mobile</em> devices &mdash; <em>assuming</em> that your template provides support for mobile devices (like the <code>responsive_classic</code> template that is built into Zen Cart 1.5.5a)!<br><br><b>Default: false</b>', $cgi, 6, now(), NULL, 'zen_cfg_select_option([\'true\', \'false\'],')"
    );

    $keys_resort_array = [
        'PRODUCTS_PAGINATION_MAX' => 10,
        'PRODUCTS_PAGINATION_MID_RANGE' => 20,
        'PRODUCTS_PAGINATION_LISTING_LINK' => 30,
        'PRODUCTS_PAGINATION_OTHER' => 40,
        'PRODUCTS_PAGINATION_OTHER_MAIN_PAGES' => 50,
        'PRODUCTS_PAGINATION_DISPLAY_PAGEDROP' => 60,
        'PRODUCTS_PAGINATION_PRODUCT_COUNT' => 70,
        'PRODUCTS_PAGINATION_COUNT_VALUES' => 80
    ];
    foreach ($keys_resort_array as $key => $sort_order) {
        $db->Execute("UPDATE " . TABLE_CONFIGURATION . " SET sort_order = $sort_order WHERE configuration_key = '$key' LIMIT 1");
    }
    define('PRODUCTS_PAGINATION_VERSION', '0.0.0');
}

// -----
// If the version noted in the database isn't the plugin's current version, update the value.
//
if (PRODUCTS_PAGINATION_VERSION !== $pp_current_version) {
    $db->Execute("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value = '$pp_current_version' WHERE configuration_key = 'PRODUCTS_PAGINATION_VERSION' LIMIT 1");
}

//----
// Register the "Product Pagination" configuration.
//
if (!zen_page_key_exists('configProdPagination')) {
    zen_register_admin_page('configProdPagination', 'BOX_CONFIGURATION_PRODUCT_PAGINATION', 'FILENAME_CONFIGURATION', "gID=$cgi", 'configuration', 'Y', $cgi);
}
