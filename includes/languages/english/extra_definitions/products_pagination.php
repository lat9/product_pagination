<?php
// -----
// Part of the "Product Pagination" plugin by lat9 (lat9@vinosdefrutastropicales.com)
// Copyright (c) 2010-2024 Vinos de Frutas Tropicales
//

define('PP_PREV_NEXT_PRODUCT', 'Currently Viewing Product ');
define('PP_PREV_NEXT_PRODUCT_SEP', ' of ');
define('PP_TEXT_PREVIOUS', 'Previous');
define('PP_TEXT_NEXT', 'Next');
define('PP_TEXT_PRODUCT_LISTING', 'View Product Listing');
define('PP_TEXT_PRODUCT_LISTING_TITLE', 'View more &quot;%s&quot;'); // %s is replaced by the categories name
define('PP_TEXT_PAGE', 'Page: ');
define('PP_TEXT_ITEMS_PER_PAGE', 'Items per Page: ');
define('PP_TEXT_ALL', 'All');

// -----
// Starting with v2.1.0 of 'Product Pagination', the split-page-results 'base' class
// is based on the zc157 version.  There were a couple of language constants added at that
// time.  If we're running under a Zen Cart version prior to zc157, define those constants.
//
if (PROJECT_VERSION_MAJOR === '1' && PROJECT_VERSION_MINOR < '5.7') {
    define('ARIA_PAGINATION_ROLE_LABEL_GENERAL','Pagination');
    define('ARIA_PAGINATION_ROLE_LABEL_FOR','%s Pagination'); // eg: "Search results Pagination"
    define('ARIA_PAGINATION_PREVIOUS_PAGE','Go to Previous Page');
    define('ARIA_PAGINATION_NEXT_PAGE','Go to Next Page');
    define('ARIA_PAGINATION_CURRENT_PAGE','Current Page');
    define('ARIA_PAGINATION_CURRENTLY_ON',', now on page %s');
    define('ARIA_PAGINATION_GOTO','Go to ');
    define('ARIA_PAGINATION_PAGE_NUM','Page %s');
    define('ARIA_PAGINATION_ELLIPSIS_PREVIOUS','Get previous group of pages');
    define('ARIA_PAGINATION_ELLIPSIS_NEXT','Get next group of pages');
    define('ARIA_PAGINATION_','');
}
