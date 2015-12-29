<?php

class Wyomind_Advancedinventory_Model_Item extends Mage_Core_Model_Abstract {

    public function _construct() {
        parent::_construct();
        $this->_init('advancedinventory/item');
    }

    public function loadByProductId($product_id) {
        $collection = Mage::getModel('advancedinventory/item')->getCollection()
                ->addFieldToFilter('product_id', Array('eq' => $product_id));
        return $collection->getFirstItem();
    }

}
