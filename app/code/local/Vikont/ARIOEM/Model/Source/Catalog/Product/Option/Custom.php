<?php

class Vikont_ARIOEM_Model_Source_Catalog_Product_Option_Custom extends Vikont_ARIOEM_Model_Source_Abstract
{

	public static function toShortOptionArray()
	{
		$productId = Mage::getResourceModel('catalog/product')
				->getIdBySku(Mage::getStoreConfig('arioem/add_to_cart/dummy_product'));

		$resource = Mage::getSingleton('core/resource');
		$connection = $resource->getConnection('core_read');

		$sql = 'SELECT cpo.option_id AS value, cpot.title AS label'
			. ' FROM `' . $resource->getTableName('catalog/product_option') .'` AS cpo '
			. ' INNER JOIN `' . $resource->getTableName('catalog/product_option_title').'` AS cpot ON cpot.option_id=cpo.option_id'
			. ' WHERE cpo.product_id=' . (int)$productId;

		try {
			$result = $connection->fetchPairs($sql);
		} catch (Exception $e) {
			Mage::logException($e);
			$result = false;
		}

		return $result;
	}

}