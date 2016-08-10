<?php
// -----
// Part of the "Products Pagination" plugin by lat9 (lat9@vinosdefrutastropicales.com)
//
if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

$autoLoadConfig[181][] = array (
    'autoType' => 'init_script',
    'loadFile' => 'init_pagination.php'
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