<?php

class Wyomind_Simplegoogleshopping_Model_System_Config_Source_Urls {

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray() {
        return array(
            array('value' => 1, 'label' => Mage::helper('simplegoogleshopping')->__('Product url')),
            array('value' => 2, 'label' => Mage::helper('simplegoogleshopping')->__('Shortest category url')),
            array('value' => 3, 'label' => Mage::helper('simplegoogleshopping')->__('Longest category url')),
        );
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray() {
        return array(
            1 => Mage::helper('simplegoogleshopping')->__('Individual product urls'),
            2 => Mage::helper('simplegoogleshopping')->__('Shortest category urls'),
            3 => Mage::helper('simplegoogleshopping')->__('Longest category urls')
            
        );
    }

}
