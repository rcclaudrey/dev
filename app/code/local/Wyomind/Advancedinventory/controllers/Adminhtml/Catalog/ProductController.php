<?php

require_once(Mage::getModuleDir('controllers','Mage_Adminhtml').DS.'Catalog/ProductController.php');
 
class Wyomind_Advancedinventory_Adminhtml_Catalog_ProductController extends Mage_Adminhtml_Catalog_ProductController
{
    public function gridAction() {
        parent::gridAction();
        $this->_redirect("*/*/index");
    }
}