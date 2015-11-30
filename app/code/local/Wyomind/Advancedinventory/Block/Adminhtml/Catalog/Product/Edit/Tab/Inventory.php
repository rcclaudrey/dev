<?php

class Wyomind_Advancedinventory_Block_Adminhtml_Catalog_Product_Edit_Tab_Inventory extends Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Inventory {

    public function fetchView($fileName) {

        extract($this->_viewVars);
        $do = $this->getDirectOutput();
        if (!$do) {
            ob_start();
        }
       
        include getcwd() . '/app/design/adminhtml/default/wyomind/template/catalog/product/tab/base-inventory.phtml';
        if (Mage::helper("core")->isModuleEnabled("Wyomind_Leadtimelabels"))
            include getcwd() . '/app/design/adminhtml/default/default/template/catalog/product/tab/leadtime.phtml';
        include getcwd() . '/app/design/adminhtml/default/wyomind/template/catalog/product/tab/stocks.phtml';
        if (!$do) {
            $html = ob_get_clean();
        } else {
            $html = '';
        }
        return $html;
    }

   

}
