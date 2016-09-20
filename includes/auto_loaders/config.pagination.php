<?php
// -----
// Part of the "Products Pagination" plugin by lat9 (lat9@vinosdefrutastropicales.com)
//
if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

// -----
// There's a bit of trickiness going on here.  The base split_page_results class has been modified so that
// it won't load if the database configuration hasn't been read, so need to load the class again
// after the database reads have been done so that the class is defined.
//
$autoLoadConfig[41][] = array (
    'autoType' => 'class',
    'loadFile' => 'split_page_results.php'
);

$autoLoadConfig[200][] = array (
    'autoType' => 'class',
    'loadFile' => 'observers/class.products_pagination_observer.php'
);

$autoLoadConfig[200][] = array (
    'autoType'   => 'classInstantiate',
    'className'  => 'products_pagination_observer',
    'objectName' => 'ppObserver'
);