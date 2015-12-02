<?php

class Vikont_ARIOEM_Helper_Cart extends Mage_Core_Helper_Abstract
{
	protected static $_oemProductId = null;



	public static function getOemProductId()
	{
		if(!self::$_oemProductId) {
			self::$_oemProductId = Mage::getResourceModel('catalog/product')
					->getIdBySku(Mage::getStoreConfig('arioem/add_to_cart/dummy_product'));
		}

		return self::$_oemProductId;
	}



	public static function isOemProduct($product)
	{
		$productId = is_object($product) ? $product->getId() : (int) $product;
		return ($productId == self::getOemProductId());
	}

}