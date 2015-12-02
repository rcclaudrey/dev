<?php

class Vikont_Pulliver_Helper_HelmetHouse extends Mage_Core_Helper_Abstract
{

	public function getLocalFileName($fileName)
	{
		return Mage::helper('pulliver')->getImportStorageLocation() . 'helmethouse/' . $fileName;
	}



	public function downloadFile()
	{
		$requestURL = trim(Mage::getStoreConfig('pulliver/helmet_house/base_url'), ' /');
		$username = Mage::getStoreConfig('pulliver/helmet_house/username');
		$password = Mage::getStoreConfig('pulliver/helmet_house/password');

		$connection = ftp_connect($requestURL);
		if(!$connection) {
			Vikont_Pulliver_Helper_Data::inform(sprintf('Could not connect to %s', $requestURL));
		}

		if(!@ftp_login($connection, $username, $password)) {
			Vikont_Pulliver_Helper_Data::throwException(sprintf('Error logging to FTP %s as %s',
					$requestURL, $username));
		}

		$remoteFileName = Mage::getStoreConfig('pulliver/helmet_house/remote_filename');
		$localFileName =  $this->getLocalFileName(basename($remoteFileName));

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

		Vikont_Pulliver_Helper_Data::type("Parsing $fileName...");

		$result = array();
		$fileHandle = fopen($fileName, 'r');

		while (false !== $values = fgetcsv($fileHandle)) {
			if(count($values) < 6) {
				continue;
			}
			$result[$values[0]] = array($values[4], $values[5]);
		}
		fclose($fileHandle);

		unset($result['Part Number']);

		return $result;
	}

}