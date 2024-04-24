<?php
// -----
// Part of the "Product Pagination" plugin by lat9 (lat9@vinosdefrutastropicales.com)
// Copyright (c) 2010-2024 Vinos de Frutas Tropicales
// 
if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

// -----
// This class, loaded by the ProductsPaginationObserver class when the plugin is enabled, overrides the name/handling of the
// Bootstrap template's zca_splitPageResults class.  It's active ONLY WHEN the plugin has been configured to provide processing 
// on "other", non-product-details type, pages and the current page is in that configuration.
//
class zca_splitPageResults extends splitPageResults
{
    public function __construct($query, $max_rows, $count_key = '*', $page_holder = 'page', $debug = false, $countQuery = '')
    {
        parent::__construct($query, $max_rows, $count_key, $page_holder, $debug, $countQuery);

        $this->currentLiClass = 'active';
    }

    protected function getUlClass(): string
    {
        $ulClass = ' class="nav nav-pills pagination"';
        if (PRODUCTS_PAGINATION_DISPLAY_PAGEDROP === 'true' || PRODUCTS_PAGINATION_PRODUCT_COUNT === 'true') {
            $ulClass = ' class="nav nav-pills pagination float-right"';
        }
        return $ulClass;
    }
    protected function drawEllipsis(string $class_list = ''): string
    {
        $class_list .= ' page-link disabled';
        return
            '<li class="page-item">' .
                '<span class="' . $class_list . '"> &hellip; </span>' .
            '</li>';
    }
    protected function formatPageLink(string $title, string $aria_label, string $name, string $page_link_parms, bool $display_flag = true, string $class_list = ''): string
    {
        global $request_type;

        if ($display_flag === false) {
            $returnValue =
                '<li class="page-item">' .
                    '<span class="page-link disabled" title="' . $title . '" aria-label="' . $aria_label . '">' .
                        $name .
                    '</span>' .
                '</li>';
        } elseif (strpos($class_list, $this->currentLiClass) !== false) {
            $returnValue =
                '<li class="page-item active">' .
                    '<span class="page-link" title="' . $title . '" aria-label="' . $aria_label . '" aria-current="true">' .
                        $name .
                    '</span>' .
                '</li>';
        } else {
            $extra_class = ($class_list !== '') ? " $class_list" : '';
            $href_link = zen_href_link($_GET['main_page'], $page_link_parms, $request_type, false);
            $returnValue =
                '<li class="page-item">' .
                    '<a href="' . $href_link . '" class="page-link' . $extra_class . '" title="' . $title . '" aria-label="' . $aria_label . '">' .
                        $name .
                    '</a>' .
                '</li>';
        }
        return $returnValue;
    }

    protected function formatDisplayLinksString(string $display_links_string, string $extra_links): string
    {
        return
            '<div class="pp-next-prev-wrap">' .
                '<div class="pp-links">' .
                    $display_links_string .
                '</div>' .
                $extra_links .
            '</div>' .
            '<div class="p-2"></div>';
    }
}
