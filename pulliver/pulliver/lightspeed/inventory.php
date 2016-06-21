<?php

ini_set('memory_limit', '2048M');
ini_set('max_execution_time', '600');
set_time_limit(0);

$mageRoot = dirname(dirname(getcwd()));

require $mageRoot . '/Vic.php';

require $mageRoot . '/app/Mage.php';
Mage::app('admin')->setUseSessionInUrl(false);
umask(0);

// = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =

Vikont_Pulliver_Model_Log::setLogFileName('pulliver.log');
Vikont_Pulliver_Helper_Data::setSilentExceptions(true);
Vikont_Pulliver_Helper_Data::inform("\nLightSpeed Convertor started...\n");

$commonHelper = Mage::helper('pulliver');
$moduleHelper = Mage::helper('pulliver/LightSpeed');
$skuHelper = Mage::helper('pulliver/Sku');

try {
	$params = isset($argv)
		? Vikont_Pulliver_Helper_Data::getCommandLineParams($argv)
		: new Varien_Object($_GET);

	Mage::register('pulliver_params', $params);

	$vendors = $params->getData('vendor')
		? explode(',', $params->getVendor())
		: Vikont_Pulliver_Helper_LightSpeed::getVendors();

	
	foreach($vendors as $vendorIndex => $vendor) {
		$isWarehouse = ('warehouse' == $vendor);

		if($downloadedFileName = $params->getData('file-' . $vendor)) {
			Vikont_Pulliver_Helper_Data::inform(sprintf('Skipped downloading, using local file %s', $downloadedFileName));
			Vikont_Pulliver_Helper_Data::inform('Decoding data for vendor: '.$vendor);
			$data = $moduleHelper->decodeFile($downloadedFileName);
		} else {
			$data = $moduleHelper->decodeJson($moduleHelper->downloadVendorRepository($vendor));
			Vikont_Pulliver_Helper_Data::inform('Decoding data for vendor: '.$vendor);
		}

		if($data) {
			if(isset($data['Message']) && $data['Message']) {
				Vikont_Pulliver_Helper_Data::inform(sprintf("ERROR getting data, error message:\n%s\n%s", $data['Message'], @$data['MessageDetail']));
				break;
			}

			Vikont_Pulliver_Helper_Data::inform(sprintf("Data decoded successfully, %d rows have been read", count($data)));
		} else {
			Vikont_Pulliver_Helper_Data::inform('ERROR: could not decode inventory');
			continue;
		}

		// file stuff
		$outputFileName = $moduleHelper->getLocalFileName('lightspeed-'.$vendor.'.csv');
		$fileHandle = $commonHelper->openFile($outputFileName);

//		$tmsReviewFileName = $moduleHelper->getLocalFileName('review-'.$vendor.'.csv');
//		$reviewFileNeedsHeader = !(file_exists($tmsReviewFileName) && filesize($tmsReviewFileName));
		$tmsReviewFileName = $moduleHelper->getLocalFileName('review-' . $vendor . '-' . time() . '.csv');
		$reviewFileNeedsHeader = true;
		$tmsReviewFileHandle = null;
		$distributorFieldNames = Vikont_Pulliver_Helper_Sku::getDistributorFieldNames();

		$lineCounter = 0;

		foreach($data as $item) {
			if($sku = $skuHelper->getSkuByItemNumber($item['SupplierCode'], $item['PartNumber'])) {
				fputcsv($fileHandle, array($sku, $item['Avail']));
				$lineCounter++;
			} else if($isWarehouse && $moduleHelper->productIsOEM($item)) {
				$skuHelper->updateOEMtable($item,  $isWarehouse);
			} else {
				if(!$tmsReviewFileHandle) {
					$tmsReviewFileHandle = fopen($tmsReviewFileName, 'ab');
					if($reviewFileNeedsHeader) {
						fputcsv($tmsReviewFileHandle, array_keys($item));
						$reviewFileNeedsHeader = false;
					}
				}
				fputcsv($tmsReviewFileHandle, $item);
			}
		}

		fclose($fileHandle);
		Vikont_Pulliver_Helper_Data::inform(sprintf('Successfully created file %s, %d lines processed, %d lines added',
			$outputFileName,
			count($data),
			$lineCounter
		));

		if($tmsReviewFileHandle) {
			fclose($tmsReviewFileHandle);
			Vikont_Pulliver_Helper_Data::inform(sprintf('Successfully created suggestions file %s', $tmsReviewFileName));
		}
	}

} catch (Exception $e) {
	Mage::logException($e);
	Vikont_Pulliver_Helper_Data::inform($e->getMessage());
}