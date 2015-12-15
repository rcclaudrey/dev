<?php

class Wyomind_Googletrustedstores_Model_System_Config_Source_Useedd {

    
    public function toOptionArray() {
        
        if (Mage::helper('googletrustedstores')->isEstimatedDeliveryDateEnabled()) {
            return array(
                array('label' => Mage::helper('googletrustedstores')->__('Yes'), 'value' => '1'),
                array('label' => Mage::helper('googletrustedstores')->__('No'), 'value' => '0')
            );
        } else {
            return array(
                array('label' => Mage::helper('googletrustedstores')->__('No'), 'value' => '0')
            );
        }
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray() {
        if (Mage::helper('googletrustedstores')->isEstimatedDeliveryDateEnabled()) {
            return array(
                array('label' => Mage::helper('googletrustedstores')->__('Yes'), 'value' => '1'),
                array('label' => Mage::helper('googletrustedstores')->__('No'), 'value' => '0')
            );
        } else {
            return array(
                array('label' => Mage::helper('googletrustedstores')->__('No'), 'value' => '0')
            );
        }
    }
    
}
