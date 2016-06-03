<?php

class Vikont_ARIOEM_Helper_OEM extends Mage_Core_Helper_Abstract
{
	protected static $_resource = null;
	protected static $_connection = null;
	protected static $_distributorFields = array(
		'punlim',
		'trocky',
		'wpower',
		'polaris',
		'canam',
		'fox',
		'hhouse',
		'honda',
		'kawasaki',
		'seadoo',
		'suzuki',
		'yamaha',
		'troylee',
	);

	protected static $_TMS2ARI = array(
		'ARC' => 'AC',
		'HOM' => array('HO', 'HW'), // honda common; ARI doesn't separate Honda Watercraft from common Honda
		'HONPE' => 'HP', // Honda Power Equipment
		'KUS' => 'KA',
		'POL' => 'PO',
		'BRP' => 'SD',
		'SUZ' => 'SU',
		'YAM' => 'YA',
		'SLN' => 'SL',
	);

	protected static $_ARI2TMS = array(
		'AC' => 'ARC',
		'HO' => 'HOM',
		'HW' => 'HOM',
		'HP' => 'HONPE',
		'KA' => 'KUS',
		'PO' => 'POL',
		'SD' => 'BRP',
		'SU' => 'SUZ',
		'YA' => 'YAM',
		'SL' => 'SLN',
	);

	protected static $_filteredItems = null;



	public static function getDistributorFields()
	{
		return self::$_distributorFields;
	}



	public static function getResource()
	{
		if(!self::$_resource) {
			self::$_resource = Mage::getSingleton('core/resource');
		}
		return self::$_resource;
	}



	public static function getConnection()
	{
		if(!self::$_connection) {
			self::$_connection = self::getResource()->getConnection('oemdb_read');
		}
		return self::$_connection;
	}



	public function getOEMCost($brand, $partNumber)
	{
		if(isset(self::$_TMS2ARI[$brand])) {
			$condition = 'supplier_code ' . (
					is_array(self::$_TMS2ARI[$brand])
						?	'IN ("'. implode('","', self::$_TMS2ARI[$brand]) .'")'
						:	'="'.addslashes(self::$_TMS2ARI[$brand]).'"'
				);
		} else {
			return false;
		}

		$sql = 'SELECT cost FROM ' . self::getResource()->getTableName('oemdb/cost')
				.' WHERE part_number="' . addslashes($partNumber) . '" AND ' . $condition;

		try {
			$result = (float) self::getConnection('oemdb_read')->fetchOne($sql);
		} catch (Exception $e) {
			Mage::logException($e);
			$result = false;
		}

		return $result;
	}



	public function getOEMCostData($brand, $partNumber)
	{
		if(!$brand || !$partNumber) {
			return false;
		}

		$brand = isset(self::$_ARI2TMS[$brand])
			?	$brand
			:	(	isset(self::$_TMS2ARI[$brand])
					?	(	is_array(self::$_TMS2ARI[$brand])
							?	reset(self::$_TMS2ARI[$brand])
							:	self::$_TMS2ARI[$brand]
						)
					:	$brand
				);

		$sql = 'SELECT * FROM ' . self::getResource()->getTableName('oemdb/cost')
				.' WHERE supplier_code="'.addslashes($brand).'" AND part_number="' . addslashes($partNumber) . '" LIMIT 1';

		try {
			$result = self::getConnection('oemdb_read')->fetchRow($sql);
		} catch (Exception $e) {
			Mage::logException($e);
			$result = false;
		}

		return $result;
	}



	public static function getPartNumbers($sku)
	{
		if(is_array($sku)) {
			$sku = array_map('addslashes', $sku);
			$condition = ' WHERE sku IN ("' . implode('","', $sku) . '")';
		} else {
			$condition = ' WHERE sku="' . addslashes($sku) . '"';
		}

		$sql = 'SELECT sku, d_' . implode(', d_', self::$_distributorFields)
				. ' FROM ' . self::getResource()->getTableName('oemdb/sku')
				. $condition;

		try {
			$result = self::getConnection('oemdb_read')->fetchAll($sql);
		} catch (Exception $e) {
			Mage::logException($e);
			$result = false;
		}

		return $result;
	}



	public static function saveImageUrl($rowId, $imageUrl)
	{
		if(!$rowId) {
			return false;
		}

		$sql = 'UPDATE ' . self::getResource()->getTableName('oemdb/cost')
				. ' SET image_url="' . addslashes($imageUrl) . '"'
				. ' WHERE id=' . addslashes($rowId);

		try {
			$result = self::getConnection('oemdb_read')->query($sql);
		} catch (Exception $e) {
			Mage::logException($e);
			$result = false;
		}
	}



	public static function TMS2ARI($brandCode)
	{
		return array_search(strtoupper($brandCode), self::$_ARI2TMS);
	}



	/**
	 * Return customer quote OEM items
	 *
	 * @return array
	 */
	public static function getCartOEMItems()
	{
		if(null === self::$_filteredItems) {
			self::$_filteredItems = array();
			$oemAttrSetId = Mage::getStoreConfig('arioem/add_to_cart/oem_product_attr_set_id');
			$items = Mage::getSingleton('checkout/session')->getQuote()->getAllVisibleItems();
			$configHelper = Mage::helper('catalog/product_configuration');

			foreach($items as $item) {
				if($oemAttrSetId == $item->getProduct()->getAttributeSetId()) {
					self::$_filteredItems[$item->getId()] = array(
						'sku' => $item->getSku(),
						'name' => $item->getName(),
						'price' => $item->getPrice(),
						'rowTotal' => $item->getRowTotal(),
						'qty' => $item->getQty(),
						'options' => Vikont_ARIOEM_Helper_Data::indexArray($configHelper->getCustomOptions($item), 'option_id'),
					);
				}
			}
		}

		return self::$_filteredItems;
	}



	public static function getSortedCartOEMItems()
	{
		$res = array();
		$brandOptionId = Mage::getStoreConfig('arioem/add_to_cart/dummy_product_brand_option_id');
		$partNumberOptionId = Mage::getStoreConfig('arioem/add_to_cart/dummy_product_partNo_option_id');

		foreach(self::getCartOEMItems() as $item) {
			$brand = $item['options'][$brandOptionId]['value'];
			$brandCode = Vikont_ARIOEM_Helper_Data::brandName2Code($brand);
			if(!isset($res[$brandCode])) {
				$res[$brandCode] = array();
			}
			$res[$brandCode][] = $item['options'][$partNumberOptionId]['value'];
		}

		return $res;
	}

}
