<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Conf
 */  
class Amasty_Conf_Model_Source_Reload extends Varien_Object
{
	public function toOptionArray()
	{
	    $hlp = Mage::helper('amconf');
		return array(
            array('value' => 'none', 'label' => $hlp->__('None')),
			array('value' => 'name', 'label' => $hlp->__('Name')),
            array('value' => 'description',  'label' => $hlp->__('Description')),
            array('value' => 'image',  'label' => $hlp->__('Image')),
            array('value' => 'short_description',  'label' => $hlp->__('Short Description')),
			array('value' => 'attributes',  'label' => $hlp->__('Attributes block')),
		);
	}
	
}