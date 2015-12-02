<?php

class Vikont_Pulliver_Helper_FoxRacing extends Mage_Core_Helper_Abstract
{

	public function getRemoteFileName($connection)
	{
		return Mage::getStoreConfig('pulliver/fox_racing/remote_filename'); // 'ProdInvFile.txt';
	}



	public function getLocalFileName($fileName)
	{
		$pathParts = pathinfo($fileName);
		return Mage::helper('pulliver')->getImportStorageLocation() . 'foxracing/' . $pathParts['basename'];
	}



	public function downloadFile()
	{
		$requestURL = trim(Mage::getStoreConfig('pulliver/fox_racing/base_url'), ' /');
		$username = Mage::getStoreConfig('pulliver/fox_racing/username');
		$password = Mage::getStoreConfig('pulliver/fox_racing/password');

		$connection = ftp_connect($requestURL);
		if(!$connection) {
			Vikont_Pulliver_Helper_Data::inform(sprintf('Could not connect to %s', $requestURL));
		}

		if(!@ftp_login($connection, $username, $password)) {
			Vikont_Pulliver_Helper_Data::throwException(sprintf('Error logging to FTP %s as %s',
					$requestURL, $username));
		}

		$remoteFileName = $this->getRemoteFileName($connection);
		if(!$remoteFileName) {
			Vikont_Pulliver_Helper_Data::throwException('Error: no remote file name set!');
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



	public function parseFile($fileName)
	{
		if(!file_exists($fileName)) {
			Vikont_Pulliver_Helper_Data::throwException(sprintf('no such data file: %s', $fileName));
			return false;
		}

		Vikont_Pulliver_Helper_Data::type("Parsing $fileName...");

		$result = array();
		$data = file($fileName, FILE_IGNORE_NEW_LINES + FILE_SKIP_EMPTY_LINES /*+ FILE_TEXT*/);

		foreach($data as $line) {
			$values = explode("\t", $line);

			if(	count($values) < 2
			||	'' === $values[2]
			) {
				continue;
			}

			$result[$values[0]] = (int) $values[2];
		}

		return $result;
	}


}