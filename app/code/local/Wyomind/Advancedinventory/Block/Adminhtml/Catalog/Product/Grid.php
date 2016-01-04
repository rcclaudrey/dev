<?php

Class Wyomind_Advancedinventory_Block_Adminhtml_Catalog_Product_Grid extends Mage_Adminhtml_Block_Catalog_Product_Grid {

    public function __construct() {
        parent::__construct();
        $this->setUseAjax(false);
    }

    public function getGridUrl() {
        return $this->getUrl('*/*/index', array('_current' => true));
    }

}
