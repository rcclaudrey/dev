<?php

class Vikont_Pulliver_Helper_OEM extends Mage_Core_Helper_Abstract
{
	protected static $_oemProductId = null;



	public static function getOemProductId()
	{
		if(!self::$_oemProductId) {
			self::$_oemProductId = Mage::getResourceModel('catalog/product')
					->getIdBySku(Mage::getStoreConfig('pulliver/oem/dummy_product'));
		}

		return self::$_oemProductId;
	}



	public static function isOemProduct($product)
	{
		return ($product->getId() == self::getOemProductId());
	}

}