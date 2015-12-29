<?php

class Wyomind_Advancedinventory_Block_Adminhtml_System_Config_Form_Field_Stocks {

    public function toOptionArray() {
        return array(
            array('value' => 1, 'label' => Mage::helper('adminhtml')->__('Local stocks assigned to store views and customer groups')),
            array('value' => 0, 'label' => Mage::helper('adminhtml')->__('Global stock')),
        );
    }

}

