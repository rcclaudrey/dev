<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Conf
 */  
class Amasty_Conf_Model_Source_Price extends Varien_Object
{
    public function toOptionArray()
    {
        $hlp = Mage::helper('amconf');
        return array(
            array('value' => 0, 'label' => $hlp->__('No')),
            array('value' => 1, 'label' => $hlp->__('For Specified Products')),
            array('value' => 2, 'label' => $hlp->__('Yes for All Products')),
        );
    }
    
}