<?php
// -----
// Part of the "Product Pagination" plugin by lat9 (lat9@vinosdefrutastropicales.com)
// Copyright (c) 2010-2024 Vinos de Frutas Tropicales
//

if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}
class products_pagination_observer extends base
{

    private $category_name;
    private $counter;
    private $cPath;
    private $id_array;
    private $isDesktop;
    private $isEnabled;
    private $isEnabledMobile;
    private $isMobile;
    private $isTablet;
    private $next_position;
    private $pagePaginationEnabled;
    private $page_link_parms;
    private $position;
    private $previous_position;
    private $products_found_count;// TODO written but never read
    private $product_names_array;

    public function __construct()
    {
        $this->id_array = [];
        $this->product_names_array = [];

        $this->attach(
            $this,
            [
                /* From /includes/init_includes/init_canonical.php */
                'NOTIFY_INIT_CANONICAL_PARAM_WHITELIST'
            ]
        );

        if (!class_exists('Mobile_Detect')) {
            require_once DIR_WS_CLASSES . 'Mobile_Detect.php';
        }
        $detect = new Detection\MobileDetect();
        $this->isTablet = $detect->isTablet() || (isset($_SESSION['layoutType']) && $_SESSION['layoutType'] === 'tablet');
        $this->isMobile = (!$detect->isTablet() && $detect->isMobile()) || (isset($_SESSION['layoutType']) && $_SESSION['layoutType'] === 'mobile');
        $this->isDesktop = !($this->isTablet || $this->isMobile);

        $this->isEnabled = (defined('PRODUCTS_PAGINATION_ENABLE') && PRODUCTS_PAGINATION_ENABLE === 'true');
        $this->isEnabledMobile = $this->isEnabled && (defined('PRODUCTS_PAGINATION_ENABLE_MOBILE') && PRODUCTS_PAGINATION_ENABLE_MOBILE === 'true');
    }

    public function update(&$class, $eventID, $p1, &$p2, &$p3, &$p4, &$p5)
    {
        switch ($eventID) {
            // -----
            // Add the pagecount variable to the canonical links' $excludeParams array.
            //
            case 'NOTIFY_INIT_CANONICAL_PARAM_WHITELIST':
                $p2[] = 'pagecount';
                break;

            default:
                break;
        }
    }

    // -----
    // This function returns a boolean value indicating whether or not the plugin is to be used on the current page.
    //
    public function isPaginationEnabled($whichType)
    {
        $enabled = ($this->isEnabled && $this->isDesktop) || ($this->isEnabledMobile && !$this->isDesktop);
        if ($enabled && $whichType === 'other' &&
            !( PRODUCTS_PAGINATION_OTHER === 'true' && PRODUCTS_PAGINATION_OTHER_MAIN_PAGES != '' && isset ($_GET['main_page']) &&
               in_array($_GET['main_page'], explode(',', PRODUCTS_PAGINATION_OTHER_MAIN_PAGES)) ) ) {
            $enabled = false;
        }
        $this->pagePaginationEnabled = $enabled;
        return $enabled;
    }

    // -----
    // This function, called by the plugin's /includes/modules/pp_product_prev_next.php, initializes the information needed to
    // display the products' next/prev links.
    //
    public function initializeNextPrev ()
    {
        global $db, $cPath, $cPath_array, $current_category_id;
        if (PRODUCT_INFO_PREVIOUS_NEXT !== '0') {
            switch ((int)PRODUCT_INFO_PREVIOUS_NEXT_SORT) {
                case (0):
                    $prev_next_order= ' order by LPAD(p.products_id,11,"0")';
                    break;
                case (1):
                    $prev_next_order= " order by pd.products_name";
                    break;
                case (2):
                    $prev_next_order= " order by p.products_model";
                    break;
                case (3):
                    $prev_next_order= " order by p.products_price_sorter, pd.products_name";
                    break;
                case (4):
                    $prev_next_order= " order by p.products_price_sorter, p.products_model";
                    break;
                case (5):
                    $prev_next_order= " order by pd.products_name, p.products_model";
                    break;
                case (6):
                    $prev_next_order= ' order by LPAD(p.products_sort_order,11,"0"), pd.products_name';
                    break;
                default:
                    $prev_next_order= " order by pd.products_name";
                    break;
            }

            if (!zen_not_null($cPath) && isset($_GET['products_id'])) {
                $cPath = zen_get_product_path((int)$_GET['products_id']);
                $cPath_array = zen_parse_category_path($cPath);
                $cPath = implode('_', $cPath_array);
                $current_category_id = $cPath_array[(count($cPath_array)-1)];
            }
            $this->page_link_parms = "cPath=$cPath&products_id=";
            $this->cPath = $cPath;

            $sql = "SELECT p.products_id, p.products_model, p.products_price_sorter, pd.products_name, p.products_sort_order
                      FROM " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_PRODUCTS_TO_CATEGORIES . " ptc
                     WHERE p.products_status = 1
                       AND p.products_id = pd.products_id
                       AND pd.language_id = " . (int)$_SESSION['languages_id'] . "
                       AND p.products_id = ptc.products_id
                       AND ptc.categories_id = " . (int)$current_category_id . $prev_next_order;
            $products_ids = $db->Execute($sql);
            $this->products_found_count = $products_ids->RecordCount();

            while (!$products_ids->EOF) {
                $this->id_array[] = $products_ids->fields['products_id'];
                $this->product_names_array[] = $products_ids->fields['products_name'];
                $products_ids->MoveNext();
            }

            if (count($this->id_array) !== 0) {
                $this->counter = 0;
                $this->position = 0;
                foreach ($this->id_array as $key => $value) {
                    if ((int)$value === (int)$_GET['products_id']) {
                        $this->position = $this->counter;
                        if ($key === 0) {
                            $this->previous_position = -1;
                        } else {
                            $this->previous_position = $key - 1;
                        }
                        if (isset ($this->id_array[$key + 1]) && $this->id_array[$key + 1]) {
                            $this->next_position = $key + 1;
                        } else {
                            $this->next_position = 0;
                        }
                    }
                    //torvista: for bad urls where the friendly url goes to a product, but the suffixed category does not contain that product: $this->previous_position and $this->next_position do not get set, BUT ONLY $this->next_position gives error from public function getNextProductInfo()
                    //e.g. https://www.website/widget?cPath=3_66_428_1830&
                    if (!isset($this->next_position)) {
                        $this->next_position = 0;
                    }
                    $last = $value;
                    $this->counter++;
                }

                $sql = "SELECT categories_name
                          FROM " . TABLE_CATEGORIES_DESCRIPTION . "
                         WHERE categories_id = " . (int)$current_category_id . "
                           AND language_id = " . (int)$_SESSION['languages_id'] . " LIMIT 1";

                $category_name_row = $db->Execute($sql);
                $this->category_name = $category_name_row->fields['categories_name'];
            }
        }
    }

    // -----
    // Return the number of products found for the previous/next display.
    //
    public function productsFoundCount()
    {
        return count($this->id_array);
    }

    // -----
    // Return the current 'counter', the number of products that can be displayed.
    //
    public function getProductsCount()
    {
        return $this->counter;
    }

    // -----
    // Return the current 'position' within the products' list.
    //
    public function currentPosition()
    {
        return $this->position;
    }

    // -----
    // Return the link to the products' listing for the cPath associated with the current set of products.
    //
    public function getListingPageLink()
    {
        return zen_href_link(FILENAME_DEFAULT, 'cPath=' . $this->cPath);
    }

    // -----
    // Return the formatted version of the current category name, used for link titles.
    //
    public function getCategoryTitle()
    {
        return htmlentities(sprintf(PP_TEXT_PRODUCT_LISTING_TITLE, $this->category_name), ENT_QUOTES, 'UTF-8', false);
    }

    // -----
    // Return the page-link parameters (the cPath and products_id) determined during
    // initialization.
    //
    public function getPageLinkParameters()
    {
        return $this->page_link_parms;
    }

    // -----
    // A collection of functions, used to retrieve the product's ID and name
    // associated with a given location in the search list.
    //
    public function getPreviousProductInfo()
    {
        return $this->getProductInfo($this->previous_position);
    }
    public function getProductInfo($offset)
    {
        return [
            'id' => $this->id_array[$offset],
            'name' => $this->product_names_array[$offset],
        ];
    }
    public function getNextProductInfo()
    {
        return $this->getProductInfo($this->next_position);
    }
}
