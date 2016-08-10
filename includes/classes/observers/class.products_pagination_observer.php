<?php
// -----
// Part of the "Products Pagination" plugin by lat9 (lat9@vinosdefrutastropicales.com)
// Copyright (c) 2016 Vinos de Frutas Tropicales
//
if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

class products_pagination_observer extends base 
{
  function __construct() 
  {
        $this->attach ($this, array ( /* From /includes/init_includes/init_canonical.php */ 'NOTIFY_INIT_CANONICAL_PARAM_WHITELIST' ));
  }
  
  function update (&$class, $eventID, $p1, &$p2, &$p3, &$p4, &$p5) 
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
}