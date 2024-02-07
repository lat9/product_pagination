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
$ppObserver->initializeNextPrev ();
