<?php

class Wyomind_Advancedinventory_Block_Adminhtml_System_Config_Form_Field_Global {

    public function toOptionArray() {
        return array(
            array('value' => 1, 'label' => Mage::helper('adminhtml')->__('Yes, it must be linked to the local stocks')),
            array('value' => 0, 'label' => Mage::helper('adminhtml')->__('No, it must be managed separately')),
        );
    }

}

