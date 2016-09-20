<?php
// -----
// Part of the "Product Pagination" plugin by lat9 (lat9@vinosdefrutastropicales.com)
// Copyright (c) 2010-2016 Vinos de Frutas Tropicales
// 
function ppHiddenVarsList() 
{
    return explode (',', 'disp_order,sort,filter_id,music_genre_id,record_company_id,typefilter,keyword,search_in_description,categories_id,inc_subcat,manufacturers_id,pfrom,pto,dfrom,dto,alpha_filter_id,main_page,cPath');
}

function ppCreateHiddenInputs ($hiddenVar) 
{
    $inputVars = '';
    foreach ($hiddenVar as $varName) {
        $inputVars .= (isset ($_GET[$varName]) ? zen_draw_hidden_field ($varName, $_GET[$varName]) : '');
    }  
    return $inputVars;
}