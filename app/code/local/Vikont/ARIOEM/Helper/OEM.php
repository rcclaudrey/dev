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



	protected static $_TMS2ARI = array(
		'HOM' => array('HO', 'HW'), // honda common; ARI doesn't separate Honda Watercraft from common Honda
		'HONPE' => 'HP', // Honda Power Equipment
		'KUS' => 'KA',
		'POL' => 'PO',
		'BRP_SEA' => 'SD',
		'SUZ' => 'SU',
		'YAM' => 'YA',
	);
//<option value="VIC">Victory</option>



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



	public function getOEMCostData($partNumber)
	{
		$sql = 'SELECT * FROM ' . self::getResource()->getTableName('oemdb/cost')
				.' WHERE part_number="' . addslashes($partNumber) . '"';

		try {
			$result = self::getConnection('oemdb_read')->fetchAll($sql);
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

}
