<?php
// -----
// Part of the "Products Pagination" plugin by lat9 (lat9@vinosdefrutastropicales.com)
// Copyright (c) 2024 Vinos de Frutas Tropicales
//
// Last updated: v3.0.0
//
// -----
// If 'Product Pagination' is not active on this page, nothing further to be done. The
// variable is set by /includes/classes/observers/ProductsPaginationObserver.php.
//
if (!isset($product_pagination_active) || empty($_GET['pagecount'])) {
    return;
}

// -----
// Otherwise, include a teeny jQuery script to dynamically add
// the hidden-field for 'pagecount' to the products' filter and
// sorter forms.
//
$pp_pagecount = ($_GET['pagecount'] === 'all') ? 'all' : ((int)$_GET['pagecount']);
?>
<script>
jQuery(document).ready(function() {
    jQuery('<input>').attr({
        name: 'pagecount',
        type: 'hidden',
        value: '<?= $pp_pagecount ?>'
    }).appendTo('form[name=filter]');

    jQuery('<input>').attr({
        name: 'pagecount',
        type: 'hidden',
        value: '<?= $pp_pagecount ?>'
    }).appendTo('form[name=sorter_form]');
});
</script>
