<?php

class Wyomind_Advancedinventory_Model_System_Config_Source_Stocks {

    public function toOptionArray() {
        return array(
            array('value' => 1, 'label' => Mage::helper('adminhtml')->__('Warehouses and POS stocks')),
            array('value' => 0, 'label' => Mage::helper('adminhtml')->__('Global Quantity')),
        );
    }
    
    public function toArray() {
        return $this->toOptionArray();
    }
}

