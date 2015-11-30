<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Sorting
 */
class Amasty_Sorting_Model_Source_Stock
{
    public function toOptionArray()
    {
        $options = array();
        
        $options[] = array(
            'value' => 0,
            'label' => Mage::helper('amsorting')->__('No'),
        );         
        $options[] = array(
            'value' => 1,
            'label' => Mage::helper('amsorting')->__('Yes'),
        );         
        $options[] = array(
            'value' => 2,
            'label' => Mage::helper('amsorting')->__('Yes for Catalog, No for Search'),
        );         

        return $options;
    }
}