<?php

class Vikont_Pulliver_Helper_Kawasaki extends Mage_Core_Helper_Abstract
{
	protected static $_formats = array(
		0 => array(5),
		1 => array(5, 9),
		2 => array(6),
		3 => array(),
		4 => array(5, 9),
		5 => array(5),
		6 => array(5, 10),
		7 => array(5, 7),
		8 => array(6, 8),
		9 => array(6, 11),
		'A' => array(6),
		'B' => array(6, 12),
		'C' => array(2),
		'D' => array(3),
		'E' => array(4, 9),
		'F' => array(4, 8)
	);



	public function getLocalFileName($fileName)
	{
		$pathParts = pathinfo($fileName);
		return Mage::helper('pulliver')->getImportStorageLocation() . 'kawasaki/' . $pathParts['basename'];
	}



	public static function formatItemNumber($value, $patternId)
	{
		$result = $value;
		$patternId = $patternId ? (is_int($patternId) ? (int) $patternId : strtoupper($patternId)) : 0;

		if(isset(self::$_formats[$patternId])) {
			foreach(self::$_formats[$patternId] as $dashPosition) {
				$result = substr_replace($result, '-', $dashPosition, 0);
			}
		}

		return $result;
	}



	public function parseFile($fileName)
	{
		if(!file_exists($fileName)) {
			Vikont_Pulliver_Helper_Data::throwException(sprintf('no such data file: %s', $fileName));
			return false;
		}

		Vikont_Pulliver_Helper_Data::type("Parsing $fileName...");

		$result = array();
		$fileHandle = fopen($fileName, 'r');

		while (false !== $values = fgetcsv($fileHandle)) {
//			$itemNumber = self::formatItemNumber($values[0], $values[1]);
//			unset($values[0]);
//			$result[$itemNumber] = $values;
//			self::convertItemNumberToSKU($itemNumber)
			$result[self::formatItemNumber($values[0], $values[1])] = $values;
		}
		fclose($fileHandle);

		unset($result['Part Number']);

		return $result;
	}



	public static function convertItemNumberToSKU($itemNumber)
	{
		return $itemNumber;
	}


}