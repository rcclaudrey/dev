<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Sorting
 */
if ('true' == (string)Mage::getConfig()->getNode('modules/Amasty_Shopby/active')) {
    class Amasty_Sorting_Block_Catalog_Product_List_Toolbar_Pure extends Amasty_Shopby_Block_Catalog_Product_List_Toolbar {}
} else {
    class Amasty_Sorting_Block_Catalog_Product_List_Toolbar_Pure extends Mage_Catalog_Block_Product_List_Toolbar {}
}