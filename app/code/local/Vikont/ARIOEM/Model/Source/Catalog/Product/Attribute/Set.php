<?php

class Vikont_ARIOEM_Model_Source_Catalog_Product_Attribute_Set extends Vikont_ARIOEM_Model_Source_Abstract
{
	protected static $_shortOptions = null;


	public static function getAllOptionValues()
	{
		return array_keys(self::toShortOptionArray());
	}


	public function toOptionArray()
	{
		if(null === $this->_options) {
			$this->_options = Mage::getModel('eav/entity_attribute_set')
                ->getResourceCollection()
                ->setEntityTypeFilter(Mage::getModel('catalog/product')->getResource()->getTypeId())
                ->load()
                ->toOptionArray();

			foreach($this->_options as $option) {
				self::$_shortOptions[$option['value']] = $option['label'];
			}
		}
		return $this->_options;
	}



	public static function toShortOptionArray()
	{
		return self::$_shortOptions;
	}

}

