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

class ProductsPaginationObserver extends base
{
    protected bool $isEnabled;
    protected bool $isEnabledMobile;
    protected bool $isBootstrapTemplate;
    protected bool $isMobile = false;
    protected bool $isTablet = false;
    protected bool $isDesktop = true;

    protected string $categoryName = '';
    protected int $previousPosition;
    protected int $nextPosition;
    protected int $counter;
    protected int $position;

    protected int $productsFoundCount = 0;
    protected array $productsArray = [];

    protected string $pageLinkParams = '';
    protected string $cPath;

    public function __construct()
    {
        $this->isEnabled = $this->isPaginationEnabled();
        if ($this->isEnabled === false || !file_exists(DIR_WS_CLASSES . 'pp_split_page_results.php')) {
            $this->isEnabled = false;
            return;
        }

        require DIR_WS_CLASSES . 'pp_split_page_results.php';

        $this->attach(
            $this,
            [
                /* From /includes/init_includes/init_canonical.php */
                'NOTIFY_INIT_CANONICAL_PARAM_WHITELIST',

                /* From /includes/modules/product_prev_next.php */
                'NOTIFY_PRODUCT_PREV_NEXT_OVERRIDE',
            ]
        );

        // -----
        // Set a global variable (used by /includes/templates/template_default/jscript/jscript_product_pagination.php)
        // to indicate that this 'alternate' pagination is active. Also used by the one template-override in
        // /includes/templates/YOUR_TEMPLATE/tpl_products_next_previous.php.
        //
        $GLOBALS['product_pagination_active'] = true;
    }
    protected function updateNotifyInitCanonicalParamWhitelist(&$class, $eventID, $current_page, &$excludeParams, &$keepableParams, &$includeCPath)
    {
        $excludeParams[] = 'pagecount';
        return;
    }
    protected function updateNotifyProductPrevNextOverride(&$class, $eventID, $dummy, &$prev_next_override)
    {
        $this->initializeNextPrev();
        $prev_next_override = true;

        return;
    }

    // -----
    // This function returns a boolean value indicating whether or not the plugin is to be used on the current page.
    //
    protected function isPaginationEnabled(): bool
    {
        // -----
        // If not installed or not enabled, not enabled!
        //
        $enabled = (defined('PRODUCTS_PAGINATION_ENABLE') && PRODUCTS_PAGINATION_ENABLE === 'true');
        if ($enabled === false) {
            return false;
        }

        // -----
        // Additional variables to be set for non-Bootstrap templates, e.g. responsive_classic.
        //
        $this->isBootstrapTemplate = (function_exists('zca_bootstrap_active') && zca_bootstrap_active() === true);
        if ($this->isBootstrapTemplate === false) {
            if (!class_exists('Mobile_Detect') && file_exists(DIR_WS_CLASSES . 'Mobile_Detect.php')) {
                require_once DIR_WS_CLASSES . 'Mobile_Detect.php';
            }
            if (class_exists('Mobile_Detect')) {
                $detect = new Mobile_Detect();
                $this->isTablet = $detect->isTablet() || (isset($_SESSION['layoutType']) && $_SESSION['layoutType'] === 'tablet');
                $this->isMobile = (!$detect->isTablet() && $detect->isMobile()) || (isset($_SESSION['layoutType']) && $_SESSION['layoutType'] === 'mobile');
                $this->isDesktop = !($this->isTablet || $this->isMobile);
            }
        }

        // -----
        // If not running on a non-Bootstrap desktop device and pagination
        // is not to be enabled on mobile devices, not enabled!
        //
        $this->isEnabledMobile = (PRODUCTS_PAGINATION_ENABLE_MOBILE === 'true');
        if ($this->isDesktop === false && $this->isEnabledMobile === false) {
            return false;
        }

        // -----
        // If the current page is a product/document information page, pagination
        // is enabled.
        //
        global $current_page_base;
        if (preg_match('/^[document_|product_].*_info$/', $current_page_base) === 1) {
            return true;
        }

        // -----
        // If non-product pages are not configured for the pagination display, pagination
        // is not enabled.
        //
        if (PRODUCTS_PAGINATION_OTHER !== 'true' || PRODUCTS_PAGINATION_OTHER_MAIN_PAGES === '') {
            return false;
        }

        // -----
        // Finally (!), we're on a non-product/document information page and 'other' pages
        // are enabled for display.  The pagination status depends on whether/not the
        // current page is configured for this pagination.
        //
        return in_array(
            $current_page_base,
            explode(
                ',',
                str_replace(
                    [
                        ' ',
                        "\n",
                        "\r",
                        "\t",
                    ],
                    '',
                    PRODUCTS_PAGINATION_OTHER_MAIN_PAGES
                )
            )
        );
    }

    // -----
    // This method, called from the notification for /includes/modules/product_prev_next.php, initializes
    // the information needed to display the products' next/prev links. Its functionality essentially
    // emulates the processing in the overridden script.
    //
    protected function initializeNextPrev()
    {
        global $db, $cPath, $cPath_array, $current_category_id;

        if (PRODUCT_INFO_PREVIOUS_NEXT !== '0') {
            $prev_next_order = zen_products_sort_order();

            if ($cPath < 1) {
                $cPath = zen_get_product_path((int)$_GET['products_id']);
                $cPath_array = zen_parse_category_path($cPath);
                $cPath = implode('_', $cPath_array);
                $current_category_id = $cPath_array[(count($cPath_array) - 1)];
            }

            $this->pageLinkParams = "cPath=$cPath&products_id=";
            $this->cPath = $cPath;

            $sql = "SELECT p.products_id
                      FROM " . TABLE_PRODUCTS . " p
                            INNER JOIN " . TABLE_PRODUCTS_DESCRIPTION . " pd
                                ON pd.products_id = p.products_id
                               AND pd.language_id = " . (int)$_SESSION['languages_id'] . "
                            INNER JOIN " . TABLE_PRODUCTS_TO_CATEGORIES . " ptc
                                ON ptc.products_id = p.products_id
                               AND ptc.categories_id = " . (int)$current_category_id . "
                     WHERE p.products_status = 1 " . $prev_next_order;
            $products_ids = $db->Execute($sql);

            $this->productsFoundCount = (int)$products_ids->RecordCount();
            if ($this->productsFoundCount === 0) {
                return;
            }

            foreach ($products_ids as $next_product) {
                $this->productsArray[] = [
                    'id' => (int)$next_product['products_id'],
                    'name' => htmlentities(zen_clean_html(zen_get_products_name($next_product['products_id'])), ENT_COMPAT, CHARSET),
                ];
            }

            $this->counter = 0;
            $this->position = 0;
            foreach ($this->productsArray as $offset => $values) {
                if ($values['id'] === (int)$_GET['products_id']) {
                    $this->position = $this->counter;
                    if ($offset === 0) {
                        $this->previousPosition = -1;
                    } else {
                        $this->previousPosition = $offset - 1;
                    }
                    if (!empty($this->productsArray[$offset + 1])) {
                        $this->nextPosition = $offset + 1;
                    } else {
                        $this->nextPosition = 0;
                    }
                }
                $this->counter++;
            }

            $this->categoryName = htmlentities(zen_clean_html(zen_get_category_name($current_category_id)), ENT_COMPAT, CHARSET);
        }
    }

    // -----
    // Return the number of products found for the previous/next display.
    //
    public function productsFoundCount(): int
    {
        return $this->productsFoundCount;
    }

    // -----
    // Return the current 'counter', the number of products that can be displayed.
    //
    public function getProductsCount(): int
    {
        return $this->counter;
    }

    // -----
    // Return the current 'position' within the products' list.
    //
    public function currentPosition(): int
    {
        return $this->position;
    }

    // -----
    // Return the link to the products' listing for the cPath associated with the current set of products.
    //
    public function getListingPageLink(): string
    {
        return zen_href_link(FILENAME_DEFAULT, 'cPath=' . $this->cPath);
    }

    // -----
    // Return the formatted version of the current category name, used for link titles.
    //
    public function getCategoryTitle(): string
    {
        return sprintf(PP_TEXT_PRODUCT_LISTING_TITLE, $this->categoryName);
    }

    // -----
    // Return the page-link parameters (the cPath and products_id) determined during
    // initialization.
    //
    public function getPageLinkParameters(): string
    {
        return $this->pageLinkParams;
    }

    // -----
    // A collection of functions, used to retrieve the product's ID and name
    // associated with a given location in the search list.
    //
    public function getPreviousProductInfo(): array
    {
        return $this->getProductInfo($this->previousPosition);
    }
    public function getProductInfo($offset): array
    {
        return $this->productsArray[$offset] ?? [];
    }
    public function getNextProductInfo(): array
    {
        return $this->getProductInfo($this->nextPosition);
    }
}
