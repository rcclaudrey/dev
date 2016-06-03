<?php

class Vikont_Fitment_Helper_Catalog_Product extends Mage_Catalog_Helper_Product
{

	/**
	 * Check if a product can be shown
	 *
	 * @param  Mage_Catalog_Model_Product|int $product
	 * @return boolean
	 */
	public function canShow($product, $where = 'catalog')
	{
		if (is_int($product)) {
			$product = Mage::getModel('catalog/product')->load($product);
		}

		/* @var $product Mage_Catalog_Model_Product */

		if (!$product->getId()) {
			return false;
		}

		$isVisibleInSite = ('fitment' == Mage::app()->getRequest()->getParam('from'))
//		$isVisibleInSite = (isset($_GET['from']) && 'fitment' == $_GET['from'])
			?	true
			:	$product->isVisibleInSiteVisibility();

		return $product->isVisibleInCatalog() && $isVisibleInSite;
	}

}