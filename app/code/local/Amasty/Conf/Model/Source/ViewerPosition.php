<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Conf
 */  
class Amasty_Conf_Model_Source_ViewerPosition extends Varien_Object
{
	public function toOptionArray()
	{
	    $hlp = Mage::helper('amconf');
		return array(
			array('value' => '1', 'label' => $hlp->__('1')),
            array('value' => '2',  'label' => $hlp->__('2')),
            array('value' => '3',  'label' => $hlp->__('3')),
            array('value' => '4',  'label' => $hlp->__('4')),
            array('value' => '5',  'label' => $hlp->__('5')),
            array('value' => '6',  'label' => $hlp->__('6')),
            array('value' => '7',  'label' => $hlp->__('7')),
            array('value' => '8',  'label' => $hlp->__('8')),
            array('value' => '9',  'label' => $hlp->__('9')),
            array('value' => '10',  'label' => $hlp->__('10')),
            array('value' => '11',  'label' => $hlp->__('11')),
			array('value' => '12',  'label' => $hlp->__('12')),
		);
	}
	
}
