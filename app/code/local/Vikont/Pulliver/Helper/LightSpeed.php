<?php

class Vikont_Pulliver_Helper_LightSpeed extends Mage_Core_Helper_Abstract
{
	protected static $_vendors = array('retail', 'warehouse');

	protected static $_OEMsupplierCodes = array('PO','SD','HO','HP','HW','YA','KA','SU');
// Polaris / Victory – PO
// Can-Am, Seadoo, Spyder – SD
// Honda – HO
// Honda Power – HP
// Honda Watercraft - HW
// Yamaha – YA
// Kawasaki – KA
// Suzuki – SU

	public static function getVendors()
	{
		return self::$_vendors;
	}



	public function downloadVendorRepository($vendorSection)
	{
		$username = Mage::getStoreConfig('pulliver/lightspeed/username');
		$password = Mage::getStoreConfig('pulliver/lightspeed/password');
		$baseUrl = Mage::getStoreConfig('pulliver/lightspeed/base_url');
		$dealerId = Mage::getStoreConfig('pulliver/lightspeed/' . $vendorSection);
		$requestURL = trim($baseUrl, ' /') . '/' . trim($dealerId); //	'https://int.lightspeedadp.com/lsapi/Part/76014846'

		$startedAt = time();

		try {
			$remoteRawData = Mage::helper('pulliver')->pullData($requestURL, $username, $password);

			$timeTaken = time() - $startedAt;

			Vikont_Pulliver_Helper_Data::inform(sprintf('Remote inventory downloaded from %s, time=%ds, size=%dbytes',
					$requestURL, $timeTaken, strlen($remoteRawData)));

			$params = Mage::registry('pulliver_params');
			if($params->getData('dump')) {
				$fileSize = strlen($remoteRawData);
				$fileName = $this->getLocalFileName(time() . '.dump');
				Mage::helper('pulliver')->saveFile($fileName, $remoteRawData);
				Vikont_Pulliver_Helper_Data::inform(sprintf('Downloaded data saved to file: %s size: %dbytes', $fileName, $fileSize));
			}
		} catch (Exception $e) {
			Vikont_Pulliver_Helper_Data::inform(sprintf('Error downloading remote inventory from %s, size=%dbytes, error message: %s',
				$requestURL, strlen($remoteRawData), $e->getMessage()));
		}

		return $remoteRawData;
	}

/* // deprecated
	public function extractData($jsonData)
	{
		if(!$jsonData) {
			Vikont_Pulliver_Helper_Data::throwException('JSON data is empty');
		}

		$data = json_decode($jsonData, true);

		if(null === $data) {
			$fileSize = strlen($jsonData);
			$fileName = $this->getLocalFileName(time() . '.dump');
			Mage::helper('pulliver')->saveFile($fileName, $jsonData);
			Vikont_Pulliver_Helper_Data::throwException(sprintf('Error JSON-decoding data, saved to file: %s size: %dbytes', $fileName, $fileSize));
			return false;
		}

		unset($jsonData);

		$pairs = array();
		foreach($data as $item) {
			$pairs[$item['PartNumber']] = $item['Avail'];
		}

		return $pairs;
	}/**/

	public function decodeJson($jsonData)
	{
		if(!$jsonData) {
			Vikont_Pulliver_Helper_Data::throwException('JSON data is empty');
		}

		$data = json_decode($jsonData, true);

		if(null === $data) {
			$fileSize = strlen($jsonData);
			$fileName = $this->getLocalFileName(time() . '.dump');
			Mage::helper('pulliver')->saveFile($fileName, $jsonData);
			Vikont_Pulliver_Helper_Data::throwException(sprintf('Error JSON-decoding data, saved to file: %s size: %dbytes', $fileName, $fileSize));
			return false;
		}

		unset($jsonData);

		return $data;
	}



	public function decodeFile($fileName)
	{
		if(!file_exists($fileName)) {
			Vikont_Pulliver_Helper_Data::throwException(sprintf('No such data file: %s', $fileName));
			return false;
		}

		$jsonData = file_get_contents($fileName);

		return $this->decodeJson($jsonData);
	}



	public function getLocalFileName($fileName)
	{
		return Mage::helper('pulliver')->getImportStorageLocation() . 'lightspeed/' . $fileName;
	}



	public function productIsOEM($data)
	{
		if(in_array($data['SupplierCode'], self::$_OEMsupplierCodes)) {
			return true;
		}
		return false;
	}


}