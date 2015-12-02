<?php

class Vikont_Wholesale_Helper_Product extends Vikont_Wholesale_Helper_Db
{

	public function findRegularProducts($skus)
	{
		$sql = 'SELECT entity_id, sku FROM ' . self::getTableName('catalog/product')
				.' WHERE sku IN ("' . implode('","', array_map('addslashes', $skus)) . '")';

		try {
			$result = self::getDbConnection()->fetchPairs($sql);
		} catch (Exception $e) {
			Mage::logException($e);
			$result = false;
		}

		return $result;
	}

}