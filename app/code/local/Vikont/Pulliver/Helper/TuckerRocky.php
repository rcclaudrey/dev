<?php

class Vikont_Pulliver_Helper_TuckerRocky extends Mage_Core_Helper_Abstract
{
	// this must correspond to system settings {pulliver/tucker_rocky/%inventory%}
	protected static $_inventories = array(
		'master',
		'inventory',
	);


	protected static $_invlistColumns = array(
		'sku' => array(0, 6),
		'tx' => 6,
		'pa' => 7,
		'or' => 8,
		'co' => 9,
		'fl' => 10,
		'il' => 11,
		'ca' => 13
	);


	protected static $_itemMasterColumns = array(
		'item' => array(0, 6, 0),
		'description' => array(6, 30, 0),
		'status' => 36,
		'hazard' => 37,
		'price_standard' => array(38, 8, 1),
		'price_best' => array(46, 8, 1),
		'uom_tr_sell' => array(54, 2, 0),
		'price_retail' => array(56, 8, 1),
		'uom_retail' => array(64, 2, 0),
		'r2s' => array(66, 6, 1),
		'weight' => array(72, 6, 1),
		'length' => array(78, 6, 1),
		'width' => array(84, 6, 1),
		'height' => array(90, 6, 1),
		'cube' => array(96, 6, 1),
		'new_segment' => array(102, 4, 0),
		'new_cagetory' => array(106, 10, 0),
		'new_subcategory' => array(116, 30, 0),
		'brand' => array(146, 30, 0),
		'brand_model' => array(176, 60, 0),
		'color_primary' => array(236, 6, 0),
		'color_secondary' => array(242, 30, 0),
		'color_pattern' => 272,
		'size_gender' => 273,
		'size' => array(274, 20, 0),
		'size_modifier' => 294,
		'vendor_part_no' => array(295, 30, 0),
		'application' => array(325, 55, 0),
	);


	protected static $_convUOM = array(
		'EA' => 'each',
		'KT' => 'kit',
		'PK' => 'pack',
		'PR' => 'pair',
		'CD' => 'card',
		'CS' => 'case',
		'DR' => 'drum',
		'YD' => 'yard'
	);


	protected static $_convNewSegment = array(
		'A' => 'ATV',
		'AU' => 'ATV Utility ATV',
		'AUT' => 'ATV UTV',
		'AS' => 'ATV Sport ATV',
		'O' => 'Offroad',
		'O2' => 'Offroad 2-stroke',
		'O4' => 'Offroad 4-stroke',
		'OA' => 'Offroad and ATV',
		'OA2' => 'Offroad / ATV 2-stroke',
		'OA4' => 'Offroad / ATV 4-stroke',
		'OD' => 'Offroad Dual Sport',
		'OE' => 'Offroad Enduro Only',
		'OX' => 'Offroad Motocross or Enduro',
		'P' => 'Powersport (or Core)',
		'N' => 'Snowmobile',
		'S' => 'Street',
		'SC' => 'Street Cruiser/Touring/V-Twin',
		'SCM' => 'Street Metric Cruiser/Touring',
		'SCMC' => 'Street Metric Cruiser',
		'SCMT' => 'Street Metric Touring',
		'SCV' => 'Street American V-Twin',
		'SCVB' => 'Street American Big Twin',
		'SCVS' => 'Street American Sportster',
		'SS' => 'Street Sportbike',
		'SSR' => 'Street Sportbike Racing',
		'W' => 'Watercraft'
	);



	public static function parseLineByPattern(&$pattern, $line)
	{
		$result = array();
		foreach($pattern as $colName => $position) {
			if(is_array($position)) {
				$value = trim(substr($line, $position[0], $position[1]));
				if($position[2]) {
					$value = (float) $value;
				}
				$result[$colName] = $value;
			} else {
				$result[$colName] = trim($line[$position]);
			}
		}
		return $result;
	}



	public function getRemoteFileName($inventoryType)
	{
		return in_array($inventoryType, self::$_inventories)
			? Mage::getStoreConfig('pulliver/tucker_rocky/' . $inventoryType)
			: false;
	}



	public function getLocalFileName($fileName)
	{
		return Mage::helper('pulliver')->getImportStorageLocation() . 'tuckerrocky/' . $fileName;
	}



	public function downloadFile($inventoryType)
	{
		$username = Mage::getStoreConfig('pulliver/tucker_rocky/username');
		$password = Mage::getStoreConfig('pulliver/tucker_rocky/password');
		$requestURL = trim(Mage::getStoreConfig('pulliver/tucker_rocky/base_url'), ' /');
		$remoteFileName = $this->getRemoteFileName($inventoryType);
		$localFileName = $this->getLocalFileName($remoteFileName);

		$connection = ftp_connect($requestURL);
		if(!$connection) {
			Vikont_Pulliver_Helper_Data::inform(sprintf('Could not connect to %s', $requestURL));
		}

		if(!@ftp_login($connection, $username, $password)) {
			Vikont_Pulliver_Helper_Data::throwException(sprintf('Error logging to FTP %s as %s',
					$requestURL, $username));
		}

		if(file_exists($localFileName)) {
			@unlink($localFileName);
		} else if(!file_exists($dirName = dirname($localFileName))) {
			mkdir($dirName, 0777, true);
		}

		Vikont_Pulliver_Helper_Data::type("Downloading $requestURL/$remoteFileName...");
		$startedAt = time();
		if(!ftp_get($connection, $localFileName, $remoteFileName, FTP_BINARY)) {
			Vikont_Pulliver_Helper_Data::throwException(sprintf('Error downloading from FTP %s/%s to %s',
					$requestURL, $remoteFileName, $localFileName));
		}
		ftp_close($connection);
		$timeTaken = time() - $startedAt;

		Vikont_Pulliver_Helper_Data::inform(sprintf('Inventory successfully downloaded from %s/%s to %s, size=%dbytes, time=%ds',
				$requestURL, $remoteFileName, $localFileName, filesize($localFileName), $timeTaken));

		return $localFileName;
	}



	public function parseFile($fileName, $inventoryType)
	{
		if(!file_exists($fileName)) {
			Vikont_Pulliver_Helper_Data::throwException(sprintf('no such data file: %s', $fileName));
			return false;
		}

		$result = array();
		$data = file($fileName, FILE_IGNORE_NEW_LINES + FILE_SKIP_EMPTY_LINES /*+ FILE_TEXT*/);

		$pattern = ($inventoryType == 'master')
			?	self::$_itemMasterColumns
			:	self::$_invlistColumns;

		Vikont_Pulliver_Helper_Data::type("Parsing $fileName...");

		foreach($data as $line) {
			$result[] = self::parseLineByPattern($pattern, $line);
		}

		return $result;
	}



	public function needsConversion($inventoryType)
	{
		return ($inventoryType == 'master');
	}



	public static function adaptVendorDataForImport($data)
	{
		return $data;
	}



}