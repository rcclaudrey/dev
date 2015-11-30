<?php
 /**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Sorting
 */

class Amasty_Sorting_Model_Source_Price
{
    public function toOptionArray()
    {
        $options = array(

            array(
                'value' => 'min_price',
                'label' => Mage::helper('amsorting')->__('Minimal Price'),
            ),
            array(
                'value' => 'price',
                'label' => Mage::helper('amsorting')->__('Price'),
            ),
            array(
                'value' => 'final_price',
                'label' => Mage::helper('amsorting')->__('Final Price'),
            ),
        );

        return $options;
    }
}