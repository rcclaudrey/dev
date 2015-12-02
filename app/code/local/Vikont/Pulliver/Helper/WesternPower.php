<?php

class Vikont_Pulliver_Helper_WesternPower extends Mage_Core_Helper_Abstract
{

	public static $columns = array(
		'QTY_BOI' => 'ID',
		'QTY_CAL' => 'CA',
		'QTY_MEM' => 'TN',
		'QTY_PEN' => 'PA',
		'QTY_IND' => 'IN',
	);



	public function getLocalFileName($fileName, $dir = '')
	{
		$pathParts = pathinfo($fileName);
		return Mage::helper('pulliver')->getImportStorageLocation() . 'westernpower/' . $dir . $pathParts['basename'];
	}



	public function downloadFile()
	{
		$requestURL = trim(Mage::getStoreConfig('pulliver/western_power/base_url'), ' /');
		$username = Mage::getStoreConfig('pulliver/western_power/username');
		$password = Mage::getStoreConfig('pulliver/western_power/password');

		$connection = ftp_connect($requestURL);
		if(!$connection) {
			Vikont_Pulliver_Helper_Data::inform(sprintf('Could not connect to %s', $requestURL));
		}

		if(!@ftp_login($connection, $username, $password)) {
			Vikont_Pulliver_Helper_Data::throwException(sprintf('Error logging to FTP %s as %s',
					$requestURL, $username));
		}

		$remoteFileName = Mage::getStoreConfig('pulliver/western_power/remote_filename');
		$localFileName = $this->getLocalFileName($remoteFileName, 'downloaded/');

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



	public function parseFile($fileName)
	{
		if(!file_exists($fileName)) {
			Vikont_Pulliver_Helper_Data::throwException(sprintf('no such data file: %s', $fileName));
			return false;
		}

		if(false === $handle = fopen($fileName, "rb")) {
			Vikont_Pulliver_Helper_Data::throwException(sprintf('Cannot open file: %s', $fileName));
			return false;
		}

		Vikont_Pulliver_Helper_Data::type("Parsing $fileName...");

		if(false !== $rowData = fgetcsv($handle, 1024, '|')) {
			$skuColumn = array_search('ITEM_NUM', $rowData);
			$columns = array();

			foreach(self::$columns as $key => $value) {
				$columns[] = array_search($key, $rowData);
			}
		} else {
			return array();
		}

		$result = array();

		while(false !== $rowData = fgetcsv($handle, 1024, '|')) {
			$data = array($rowData[$skuColumn]);

			foreach($columns as $columnPos) {
				$data[] = ('+' == $rowData[$columnPos]) ? 10 : (int)$rowData[$columnPos];
			}

			$result[] = $data;
		}

		return $result;
	}


}