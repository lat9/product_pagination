DELETE FROM configuration WHERE configuration_key LIKE 'PRODUCTS_PAGINATION_%';
DELETE FROM configuration_group WHERE configuration_group_title = 'Products Pagination' LIMIT 1;
DELETE FROM admin_pages WHERE page_key='configProdPagination' LIMIT 1;