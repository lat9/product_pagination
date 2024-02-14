<?php
// -----
// Part of the "Product Pagination" plugin by lat9 (lat9@vinosdefrutastropicales.com)
// Copyright (c) 2010-2024 Vinos de Frutas Tropicales
//

if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

// -----
// There's a bit of trickiness going on here.  The base split_page_results class has been modified so that
// it won't load if the database configuration hasn't been read, so need to load the class again
// after the database reads have been done and the session is established so that the class is defined.
//
// Since that class uses a common function provided by the pagination observer, the split_page_results
// class needs to be loaded after that.
//
// Note:  Adding that 'forceLoad' element for continued operation under zc157 (and later?).
//
$autoLoadConfig[117][] = array (
    'autoType' => 'class',
    'loadFile' => 'split_page_results.php',
    'forceLoad' => true
);

$autoLoadConfig[116][] = array (
    'autoType' => 'class',
    'loadFile' => 'observers/class.products_pagination_observer.php'
);

$autoLoadConfig[116][] = array (
    'autoType'   => 'classInstantiate',
    'className'  => 'products_pagination_observer',
    'objectName' => 'ppObserver'
);