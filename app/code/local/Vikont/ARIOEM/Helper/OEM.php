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
		'BRP_SEA' => 'SD',
		'SLN' => 'SL',
		'SUZ' => 'SU',
		'YAM' => 'YA',
	);

	protected static $_ARI2TMS = array(
		'AC' => 'ARC',
		'HO' => 'HOM',
		'HP' => 'HONPE',
		'HW' => 'HOM',
		'KA' => 'KUS',
		'PO' => 'POL',
		'SD' => 'BRP',
//		'SD' => 'BRP_SEA',
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



	public function getOEMData($brand, $partNumber)
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
	public function getCartOEMItems()
	{
		if(null === self::$_filteredItems) {
			self::$_filteredItems = array();
			$oemAttrSetId = Mage::getStoreConfig('arioem/add_to_cart/oem_product_attr_set_id');
			$items = Mage::getSingleton('checkout/session')->getQuote()->getAllVisibleItems();
			$configHelper = Mage::helper('catalog/product_configuration');

			$brandOptionId  = Mage::getStoreConfig('arioem/add_to_cart/dummy_product_brand_option_id');
			$partNumberOptionId  = Mage::getStoreConfig('arioem/add_to_cart/dummy_product_partNo_option_id');

			foreach($items as $item) {
				if($oemAttrSetId == $item->getProduct()->getAttributeSetId()) {
					$qty = $item->getQty();
					$options = Vikont_ARIOEM_Helper_Data::indexArray($configHelper->getCustomOptions($item), 'option_id');
					$brandCode = Vikont_ARIOEM_Model_Source_Oembrand::getOptionCode($options[$brandOptionId]['value']);
					$partNumber = $options[$partNumberOptionId]['value'];
					$oemData = $this->getOEMData($brandCode, $partNumber);

					self::$_filteredItems[$item->getId()] = array(
						'brandCode' => $brandCode,
						'partNumber' => $partNumber,
						'name' => $item->getName(),
						'price' => $item->getPrice(),
						'rowTotal' => $item->getRowTotal(),
						'qty' => $qty,
					);
				}
			}
		}

		return self::$_filteredItems;
	}



	public function getSortedCartOEMItems()
	{
		$res = array();

		foreach($this->getCartOEMItems() as $item) {
			$brandCode = $item['brandCode'];
			$partNumber = $item['partNumber'];

			if(!isset($res[$brandCode])) {
				$res[$brandCode] = array();
			}

			$res[$brandCode][] = $partNumber;
		}

		return $res;
	}



	public function decreaseInventoryValues($qtys)
	{
		$sql = 'UPDATE IGNORE ' . self::getResource()->getTableName('oemdb/cost')
			. ' SET inv_local = GREATEST(0, inv_local - :QTY)'
			. ' WHERE supplier_code=:BRAND AND part_number=:PART_NUMBER';

		foreach($qtys as $brand => $parts) {
			$brandShortCode = isset(self::$_ARI2TMS[$brand])
				?	$brand
				:	(	isset(self::$_TMS2ARI[$brand])
						?	(	is_array(self::$_TMS2ARI[$brand])
								?	reset(self::$_TMS2ARI[$brand])
								:	self::$_TMS2ARI[$brand]
							)
						:	$brand
					);

			foreach($parts as $partNumber => $qty) {
				self::getConnection('oemdb_read')->query($sql, array(
					':BRAND' => $brandShortCode,
					':PART_NUMBER' => $partNumber,
					':QTY' => (int)$qty,
				));
			}
		}
	}

}
