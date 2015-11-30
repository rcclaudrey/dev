<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Conf
 */  
class Amasty_Conf_Model_Source_ChangeWith extends Varien_Object
{
	public function toOptionArray()
	{
	    $hlp = Mage::helper('amconf');
		return array(
			array('value' => 'mouse', 'label' => $hlp->__('On Mouse Over')),
			array('value' => 'click',  'label' => $hlp->__('On Click')),
		);
	}
	
}