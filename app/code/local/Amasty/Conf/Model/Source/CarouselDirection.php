<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Conf
 */  
class Amasty_Conf_Model_Source_CarouselDirection extends Varien_Object
{
	public function toOptionArray()
	{
	    $hlp = Mage::helper('amconf');
		return array(
			array('value' => 'under', 'label' => $hlp->__('Under the main image')),
			array('value' => 'left', 'label' => $hlp->__('To the left of the main image')),
		);
	}
	
}