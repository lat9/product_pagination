<?php
// -----
// Part of the "Product Pagination" plugin by lat9 (lat9@vinosdefrutastropicales.com)
// Copyright (c) 2010-2020 Vinos de Frutas Tropicales
//
$products_found_count = $ppObserver->productsFoundCount();
if ($products_found_count > 1) {
    $products_last_index = $products_found_count - 1;

    $current_position = $ppObserver->currentPosition();
    $display_next_link  = $current_position !== $products_last_index;
    
    $products_count = $ppObserver->getProductsCount();
    
    $page_link_parms = $ppObserver->getPageLinkParameters();
    
    $listing_link_class = (PRODUCTS_PAGINATION_LISTING_LINK === 'true') ? ' class="back pagination-list"' : '';
?>
<div class="ppNextPrevWrapper">
    <div class="ppNextPrevCounter">
        <p<?php echo $listing_link_class; ?>><?php echo PP_PREV_NEXT_PRODUCT . ($current_position+1) . PP_PREV_NEXT_PRODUCT_SEP . $products_count; ?></p>
<?php
    if (PRODUCTS_PAGINATION_LISTING_LINK === 'true') {
?>
        <div class="prod-pagination prevnextReturn">
            <ul>
                <li><a href="<?php echo $ppObserver->getListingPageLink(); ?>" class="prevnext" title="<?php echo $ppObserver->getCategoryTitle(); ?>"><?php echo PP_TEXT_PRODUCT_LISTING; ?></a></li>
            </ul>
        </div>
<?php
    }
?>
        <div class="clearBoth"></div>
    </div>

    <nav class="pagination">
        <ul>
<?php
    if ($current_position !== 0) {
        $previous_info = $ppObserver->getPreviousProductInfo();
        $ppID = $previous_info['id'];
        $product_link = zen_href_link(zen_get_info_page($ppID), $page_link_parms . $ppID, 'NONSSL', false);
?>
            <li><a href="<?php echo $product_link; ?>" class="prevnext" title="<?php echo htmlentities(zen_clean_html($previous_info['name']), ENT_COMPAT, CHARSET); ?>"><?php echo PP_TEXT_PREVIOUS; ?></a></li>
<?php
    } else {
?>
        <li><span class="prevnext disablelink"><?php echo PP_TEXT_PREVIOUS; ?></span></li>
<?php
    }

    if ($products_found_count <= (int)PRODUCTS_PAGINATION_MAX) {
        for ($i = 0; $i < $products_found_count; $i++) {
            $item_class = ($i === $current_position) ? ' class="currentpage"' : '';
            $p_info = $ppObserver->getProductInfo($i);
            $ppID = $p_info['id'];
            $product_link = zen_href_link(zen_get_info_page($ppID), $page_link_parms . $ppID, 'NONSSL', false);
?>
        <li><a href="<?php echo $product_link; ?>"<?php echo $item_class; ?> title="<?php echo htmlentities(zen_clean_html($p_info['name']), ENT_COMPAT, CHARSET); ?>"><?php echo $i + 1; ?></a></li>
<?php
        }
    } else {
        $first_product_link = $current_position - floor((int)PRODUCTS_PAGINATION_MID_RANGE / 2);
        $last_product_link  = $current_position + floor((int)PRODUCTS_PAGINATION_MID_RANGE / 2);

        if ($first_product_link < 0) {
            $last_product_link += abs($first_product_link);
            $first_product_link = 0;
        }
        if ($last_product_link > $products_last_index) {
            $first_product_link -= $last_product_link - $products_last_index;
            $last_product_link   = $products_last_index;
        }
        $display_range = range($first_product_link, $last_product_link); //note: array values are doubles!

        for ($i = 0; $i < $products_found_count; $i++) {
            if ($display_range[0] > 1 && $i == $display_range[0]) {
?>
        <li class="hellip"> ... </li>
<?php
            }
            // loop through all pages. if first, last, or in range, display
            if ($i === 0 || $i === $products_last_index || in_array($i, $display_range)) {
                $item_class = ($i === $current_position) ? ' class="currentpage"' : '';
                $p_info = $ppObserver->getProductInfo($i);
                $ppID = $p_info['id'];
                $product_link = zen_href_link(zen_get_info_page($ppID), $page_link_parms . $ppID, 'NONSSL', false);
?>
        <li><a href="<?php echo $product_link; ?>"<?php echo $item_class; ?> title="<?php echo htmlentities(zen_clean_html($p_info['name']), ENT_COMPAT, CHARSET); ?>"><?php echo $i + 1; ?></a></li>
<?php
            }
            
            if ((int)$display_range[PRODUCTS_PAGINATION_MID_RANGE-1] < $products_last_index-1 && $i === (int)$display_range[PRODUCTS_PAGINATION_MID_RANGE-1]) {
?>
        <li class="hellip"> ... </li>
<?php
            }
        }
    } 

    if ($current_position !== $products_last_index) {
        $next_info = $ppObserver->getNextProductInfo();
        $ppID = $next_info['id'];
        $product_link = zen_href_link(zen_get_info_page($ppID), $page_link_parms . $ppID, 'NONSSL', false);
?>
            <li><a href="<?php echo $product_link; ?>" class="prevnext" title="<?php echo htmlentities(zen_clean_html($next_info['name']), ENT_COMPAT, CHARSET); ?>"><?php echo PP_TEXT_NEXT; ?></a></li>
<?php
    } else {
?>
        <li><span class="prevnext disablelink"><?php echo PP_TEXT_NEXT; ?></span></li>
<?php } ?>
        </ul>
    </div>
    <div class="clearBoth"></div>
</div>
<?php
}
