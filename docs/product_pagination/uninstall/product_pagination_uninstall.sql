SELECT @cgi:=configuration_group_id
FROM configuration_group WHERE configuration_group_title="Products Pagination";
DELETE FROM configuration WHERE configuration_group_id=@cgi AND configuration_group_id != 0;
DELETE FROM configuration_group WHERE configuration_group_id=@cgi AND configuration_group_id != 0;
DELETE FROM admin_pages WHERE page_key='configProdPagination';