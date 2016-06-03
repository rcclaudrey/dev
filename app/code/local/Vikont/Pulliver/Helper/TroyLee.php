<?php

class Vikont_Pulliver_Helper_TroyLee extends Mage_Core_Helper_Abstract
{
	protected static $_columnNames = array(
		'Part (B)',
		'Avail',
	);
	protected static $_columnPositions = array(1, 4);



	public function getRemoteFileName($connection)
	{
		$fileList = ftp_nlist($connection, '/');

		if(!count($fileList)) {
			return false;
		}

		$now = time();

		for($i=0; $i<300; $i++) {
//			$fileName = strtoupper(date('F j', $now)) . '.csv';
			$fileName = strtoupper(date('F d', $now)) . '.csv';
			if(in_array($fileName, $fileList)) {
				return $fileName;
			}
			$now -= 24*60*60;
		}

		return false;
	}



	public function getLocalFileName($fileName)
	{
		$pathParts = pathinfo($fileName);
		return Mage::helper('pulliver')->getImportStorageLocation() . 'troylee/' . $pathParts['basename'];
	}



	public function downloadFile()
	{
		$requestURL = trim(Mage::getStoreConfig('pulliver/troy_lee/base_url'), ' /');
		$username = Mage::getStoreConfig('pulliver/troy_lee/username');
		$password = Mage::getStoreConfig('pulliver/troy_lee/password');

		$connection = ftp_connect($requestURL);
		if(!$connection) {
			Vikont_Pulliver_Helper_Data::inform(sprintf('Could not connect to %s', $requestURL));
		}

		if(!@ftp_login($connection, $username, $password)) {
			Vikont_Pulliver_Helper_Data::throwException(sprintf('Error logging to FTP %s as %s',
					$requestURL, $username));
		}

		ftp_pasv($connection, true);

		$remoteFileName = $this->getRemoteFileName($connection);
		if(!$remoteFileName) {
			Vikont_Pulliver_Helper_Data::throwException('Error: no appropriate remote file found');
			return false;
		}

		$localFileName = $this->getLocalFileName($remoteFileName);

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



	protected function _detectColumnPositions($cols)
	{
		foreach(self::$_columnNames as $colIndex => $colName) {
			if(false !== $colPosition = array_search($colName, $cols)) {
				self::$_columnPositions[$colIndex] = $colPosition;
			}
		}
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
		$firstLine = true;

		while (false !== $values = fgetcsv($fileHandle)) {
			if($firstLine) {
				$this->_detectColumnPositions($values);
				$firstLine = false;
				continue;
			}
			$result[$values[self::$_columnPositions[0]]] = (int) trim($values[self::$_columnPositions[1]], '+');
		}

		fclose($fileHandle);

		return $result;
	}

}