SELECT (@cgi:=configuration_group_id) as cgi 
FROM configuration_group
WHERE configuration_group_title= 'Products Pagination';
DELETE FROM configuration WHERE configuration_group_id = @cgi AND configuration_group_id != 0;
DELETE FROM configuration_group WHERE configuration_group_id = @cgi AND configuration_group_id != 0;

INSERT INTO configuration_group (configuration_group_title, configuration_group_description, sort_order, visible) VALUES ('Products Pagination', 'Configure Products Pagination Settings', '1', '1');
UPDATE configuration_group SET sort_order = last_insert_id() WHERE configuration_group_id = last_insert_id();
SELECT @cgi := configuration_group_id FROM configuration_group WHERE configuration_group_title = 'Products Pagination';

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added) 
VALUES ('Products Pagination &mdash; Maximum Links', 'PRODUCTS_PAGINATION_MAX', '10', 'This is the maximum number of product links to be displayed before pagination begins.  This value should be greater than the number of <em>Intermediate Links</em>.<br /><br /><b>Default: 10</b><br />', @cgi, '1', NOW(), NOW()
);

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added) 
VALUES ('Products Pagination &mdash; Intermediate Links', 'PRODUCTS_PAGINATION_MID_RANGE', '7', 'This is the number of intermediate links to be shown when the number of products in the current category is greater than the <em>Maximum Links</em>; the first and last product link is always shown.  The value should be an odd number for link symmetry.<br /><br /><b>Default: 7</b><br />', @cgi, '2', NOW(), NOW());

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified,  use_function, set_function, date_added) VALUES ('Enable product listing link?', 'PRODUCTS_PAGINATION_LISTING_LINK', 'true', 'If enabled, a &quot;View Product listing&quot; link is shown on the same line as &quot;Viewing product x of y&quot;.<br /><br /><b>Default: true</b>', @cgi, '3', NOW(), NULL, 'zen_cfg_select_option(array(''true'', ''false''), ', NOW());

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified,  use_function, set_function, date_added) VALUES ('Enable links on other pages?', 'PRODUCTS_PAGINATION_OTHER', 'true', 'If enabled, the &quot;Other pages to link&quot; will have the pagination links applied.<br /><br /><b>Default: true</b>', @cgi, '4', NOW(), NULL, 'zen_cfg_select_option(array(''true'', ''false''), ', NOW());

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added) 
VALUES ('Other pages to link', 'PRODUCTS_PAGINATION_OTHER_MAIN_PAGES', 'account_history,advanced_search_result,featured_products,index,product_reviews,products_all,products_new,reviews,specials', 'This comma-separated list identifies the &quot;other&quot; pages to which the pagination display should be applied.', @cgi, '5', NOW(), NOW());

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified,  use_function, set_function, date_added) VALUES ('Include page-select drop-down?', 'PRODUCTS_PAGINATION_DISPLAY_PAGEDROP', 'false', 'If enabled, a drop-down menu is displayed on the <strong>other</strong> pages to allow the customer to go to a specific page number.<br /><b>Default: false</b>', @cgi, '6', NOW(), NULL, 'zen_cfg_select_option(array(''true'', ''false''), ', NOW());

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified,  use_function, set_function, date_added) VALUES ('Include item-count drop-down?', 'PRODUCTS_PAGINATION_PRODUCT_COUNT', 'false', 'If enabled, a drop-down menu is displayed to allow the customer to choose the number of items displayed for the <strong>other</strong> pages.  The count choices are contained in &quot;Item Counts&quot; (see below).<br /><b>Default: false</b>', @cgi, '7', NOW(), NULL, 'zen_cfg_select_option(array(''true'', ''false''), ', NOW());

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added) 
VALUES ('Item counts', 'PRODUCTS_PAGINATION_COUNT_VALUES', '10,25,50,100,*', 'This comma-separated list identifies the item-count choices that will be displayed in a drop-down menu to the customer.  The value \'*\' corresponds to <em>All</em> the items being displayed.', @cgi, '8', NOW(), NOW());