<?php

ini_set('memory_limit', '2048M');
ini_set('max_execution_time', '600');
set_time_limit(0);

$mageRoot = dirname(dirname(getcwd()));

require $mageRoot . '/app/Mage.php';
Mage::app('admin')->setUseSessionInUrl(false);
umask(0);

require $mageRoot . '/Vic.php';

// = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =

Vikont_Pulliver_Model_Log::setLogFileName('pulliver.log');
Vikont_Pulliver_Helper_Data::setSilentExceptions(true);
Vikont_Pulliver_Helper_Data::inform("\nLightSpeed Connector started...\n");

$commonHelper = Mage::helper('pulliver');
$moduleHelper = Mage::helper('pulliver/LightSpeed');

try {
	$params = isset($argv)
		? Vikont_Pulliver_Helper_Data::getCommandLineParams($argv)
		: new Varien_Object($_GET);

	Mage::register('pulliver_params', $params);

//	if($downloadedFileName = $params->getData('file')) {
//		Vikont_Pulliver_Helper_Data::inform(sprintf('Skipped downloading, using local file %s', $downloadedFileName));
//	} else {
//		$downloadedFileName = $moduleHelper->downloadFile();
//	}

//	Vikont_Pulliver_Helper_Sku::loadIds('distri_num_tucker_rocky');
	$result = array();

	foreach(Vikont_Pulliver_Helper_LightSpeed::getVendors() as $vendorIndex => $vendor) {
		$columnIndex = $vendorIndex + 1;

		$data = $moduleHelper->extractData($moduleHelper->downloadVendorRepository($vendor));
		if(!$data) {
			Vikont_Pulliver_Helper_Data::inform('could not decode inventory from vendor: '.$vendor);
			continue;
		}
		Vikont_Pulliver_Helper_Data::inform('data decoded successfully for vendor: '.$vendor);
/*
		foreach($data as $itemNumber => $qty) {
			$id = Vikont_Pulliver_Helper_Sku::getIdByItemNumber($itemNumber);
			if($id) {
				$sku = Vikont_Pulliver_Helper_Sku::getSkuById($id);
			} else {
				// no such vendor ID assigned to any product
				// we probably need some conversion to correspond vendor Item Number to Magento SKU
//				$convertedSKU = Vikont_Pulliver_Helper_LightSpeed::convertItemNumberToSKU($itemNumber);
//				$productId = Vikont_LightSpeedConnector_Helper_Sku::getIdBySku($convertedSKU);
//				$sku = $productId ? $convertedSKU : null;

				$sku = $itemNumber;
			}

			if($sku) {
				if(!isset($result[$itemNumber])) {
					$result[$itemNumber] = array($sku);
				}

				// to avoid missing columns like: sku, {missed column}, qty2
				// actual for rows where not all vendors supply data
				for($missingColumnIndex = count($result[$itemNumber]); $missingColumnIndex < $columnIndex; $missingColumnIndex++) {
					$result[$itemNumber][$missingColumnIndex] = 0;
				}

				$result[$itemNumber][$columnIndex] = $qty;
			} else {
				// no SKU found for this item number
				Vikont_Pulliver_Helper_Data::inform('no SKU found for item number: '.$itemNumber);
			}
		}
/**/
		unset($data);
	}

	// file stuff
	$fileName = $moduleHelper->getLocalFileName('lightspeed.csv');
	$fileHandle = $commonHelper->openFile($fileName);

	foreach($result as $item) {
		fwrite($fileHandle, implode(',', $item)."\n");
	}

	fclose($fileHandle);

	Vikont_Pulliver_Helper_Data::inform(sprintf('Successfully created file %s having %d lines', $fileName, count($result)));

} catch (Exception $e) {
	Mage::logException($e);
	Vikont_Pulliver_Helper_Data::inform($e->getMessage());
}