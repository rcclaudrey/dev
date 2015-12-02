<?php

class Vikont_Wholesale_Helper_OEM extends Mage_Core_Helper_Abstract
{
	protected static $_resource = null;
	protected static $_connection = null;

	protected static $_distributorFieldNames = array(
		'punlim' => 'Parts Unlimited',
		'trocky' => 'Tucker Rocky',
		'wpower' => 'Western Power Sports',
		'polaris' => 'Polaris',
		'canam' => 'Can-Am',
		'fox' => 'Fox Racing',
		'hhouse' => 'Helmet House',
		'honda' => 'Honda',
		'kawasaki' => 'Kawasaki',
		'seadoo' => 'SeaDoo',
		'suzuki' => 'Suzuki',
		'yamaha' => 'Yamaha',
		'troylee' => 'Troy Lee',
	);

	protected static $_supplierNames = array(
		'HO' => 'Honda',
		'HP' => 'Honda Power Equipment',
		'HW' => 'Honda Watercraft',
		'KA' => 'Kawasaki',
		'PO' => 'Polaris',
		'SD' => 'SeaDoo',
		'SU' => 'Suzuki',
		'YA' => 'Yamaha',
	);



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



	public static function getARI2TMSCode($code)
	{
		foreach(self::$_TMS2ARI as $ari => $tms) {
			if(is_array($tms) && in_array($code, $tms) || $code === $tms) {
				return $ari;
			}
		}
		return $code;
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



	public function findPart($partNumber)
	{
		$sql = 'SELECT supplier_code, part_number, cost FROM ' . self::getResource()->getTableName('oemdb/cost')
				.' WHERE part_number="' . addslashes($partNumber) . '"';

		try {
			$result = self::getConnection('oemdb_read')->fetchAll($sql);
		} catch (Exception $e) {
			Mage::logException($e);
			$result = false;
		}

		return $result;
	}



	public function findParts($partNumbers)
	{
		$sql = 'SELECT part_number, supplier_code, cost FROM ' . self::getResource()->getTableName('oemdb/cost')
				.' WHERE part_number IN ("' . implode('","', array_map('addslashes', $partNumbers)) . '")';

		try {
			$result = self::getConnection('oemdb_read')->fetchAll($sql);
		} catch (Exception $e) {
			Mage::logException($e);
			$result = false;
		}

		return $result;
	}



	public function getSkuByPartNumber($itemNumber)
	{
		$resource = Mage::getSingleton('core/resource');
		$connection = $resource->getConnection('oemdb_read');

		try {
			$where = array();
			$itemNumber = addslashes($itemNumber);

			foreach(self::$_distributorFieldNames as $fieldName => $vendorName) {
				$where[] = 'd_' . $fieldName . '="'.$itemNumber.'"';
			}

			$sql = 'SELECT sku FROM '.$resource->getTableName('oemdb/sku') .' WHERE ' . implode(' OR ', $where) . ' LIMIT 1';

			return $connection->fetchOne($sql);
		} catch (Exception $e) {
			Mage::logException($e);
		}
		return false;
	}



	public function getSkusByPartNumber($itemNumber)
	{
		$resource = Mage::getSingleton('core/resource');
		$connection = $resource->getConnection('oemdb_read');

		try {
//			$parts = array();
			$where = array();
			$itemNumber = addslashes($itemNumber);
			$tableName = $resource->getTableName('oemdb/sku');

//			foreach(self::$_distributorFieldNames as $fieldName => $vendorName) {
//				$parts[] = 'SELECT DISTINCT sku, "'.$fieldName.'" AS distributor FROM '.$tableName.' WHERE d_' . $fieldName . '="'.$itemNumber.'"';
//			}
//			$sql = implode(' UNION ', $parts);

			foreach(self::$_distributorFieldNames as $fieldName => $vendorName) {
				$where[] = 'd_' . $fieldName . '="'.$itemNumber.'"';
			}

			$sql = 'SELECT DISTINCT sku FROM '.$resource->getTableName('oemdb/sku') .' WHERE ' . implode(' OR ', $where);

			return $connection->fetchAll($sql);
		} catch (Exception $e) {
			Mage::logException($e);
		}
		return false;
	}



	public static function getSupplierName($supplierCode)
	{
		return isset(self::$_supplierNames[$supplierCode])
			?	self::$_supplierNames[$supplierCode]
			:	$supplierCode;
	}
}
