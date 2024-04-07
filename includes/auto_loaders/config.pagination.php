<?php
// -----
// Part of the "Products Pagination" plugin by lat9 (lat9@vinosdefrutastropicales.com)
// Copyright (c) 2016-2024 Vinos de Frutas Tropicales
//
// Last updated: v3.0.0
//
if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

// -----
// Note that init_split_page_results.php runs at CP 115.  It'll load
// the base ZC version of the class if not already present.
//
$autoLoadConfig[113][] = [
    'autoType' => 'class',
    'loadFile' => 'observers/ProductsPaginationObserver.php'
];

$autoLoadConfig[113][] = [
    'autoType'   => 'classInstantiate',
    'className'  => 'ProductsPaginationObserver',
    'objectName' => 'ppObserver'
];
