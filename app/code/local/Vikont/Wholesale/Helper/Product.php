<?php

class Vikont_Wholesale_Helper_Product extends Vikont_Wholesale_Helper_Db
{

	public function findRegularProducts($skus)
	{
		$attributeModel = Mage::getModel('eav/entity_attribute')->loadByCode(Mage_Catalog_Model_Product::ENTITY, 'ari_part_number');

		$sql = 'SELECT entity_id, value FROM ' . self::getTableName('catalog_product_entity_' . $attributeModel->getBackendType())
				. ' WHERE attribute_id=' . $attributeModel->getAttributeId() . ' AND store_id=0'
				. ' AND value IN ("' . implode('","', array_map('addslashes', $skus)) . '")';

		try {
			$result = self::getDbConnection()->fetchPairs($sql);
		} catch (Exception $e) {
			Mage::logException($e);
			$result = false;
		}

		return $result;
	}



	public function findProductIdByAttributeValue($attrCode, $attrValue)
	{
		try {
			$attributeModel = Mage::getModel('eav/entity_attribute')->loadByCode(Mage_Catalog_Model_Product::ENTITY, $attrCode);

			$sql = 'SELECT entity_id FROM ' . self::getTableName('catalog_product_entity_' . $attributeModel->getBackendType())
					. ' WHERE attribute_id=' . $attributeModel->getAttributeId() . ' AND store_id=0 AND value="' . addslashes($attrValue) . '"'
					. ' LIMIT 1';

			$result = (int) self::getDbConnection()->fetchOne($sql);
		} catch (Exception $e) {
			Mage::logException($e);
			$result = false;
		}

		return $result;
	}

}