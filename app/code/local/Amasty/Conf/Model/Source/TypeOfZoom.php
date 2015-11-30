<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Conf
 */  
class Amasty_Conf_Model_Source_TypeOfZoom extends Varien_Object
{
	public function toOptionArray()
	{
	    $hlp = Mage::helper('amconf');
		return array(
            array('value' => 'window',  'label' => $hlp->__('Outside')),
            array('value' => 'inner', 'label' => $hlp->__('Inside')),
			array('value' => 'lens',  'label' => $hlp->__('Lens')),
		);
	}
	
}