<?php

class Wyomind_Advancedinventory_Block_Adminhtml_System_Config_Form_Field_Syncing {

    public function toOptionArray() {
        return array(
            array('value' => 1, 'label' => Mage::helper('adminhtml')->__('Automatically')),
            array('value' => 0, 'label' => Mage::helper('adminhtml')->__('Manually (by myself)')),
        );
    }

}

