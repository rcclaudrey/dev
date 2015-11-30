<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Conf
 */  
class Amasty_Conf_Model_Source_TitlePosition extends Varien_Object
{
	public function toOptionArray()
	{
	    $hlp = Mage::helper('amconf');
		return array(
			array('value' => 'float', 'label' => $hlp->__('Float')),
			array('value' => 'inside', 'label' => $hlp->__('Inside')),
            array('value' => 'outside', 'label' => $hlp->__('Outside')),
            array('value' => 'over', 'label' => $hlp->__('Over')),
		);
	}
	
}