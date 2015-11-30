<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Sorting
 */
class Amasty_Sorting_Model_Source_State
{
    public function toOptionArray()
    {
        $options = array(
            array('value' => '', 'label' => '')
        );

        foreach (Mage::getConfig()->getNode('global/sales/order/states')->children() as $state => $node) {
            $label = Mage::helper('amsorting')->__(trim( (string) $node->label ) );
            $options[] = array('value' => $state, 'label' => $label);
        }

        return $options;
    }
}