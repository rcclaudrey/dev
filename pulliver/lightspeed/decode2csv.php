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
Vikont_Pulliver_Helper_Data::inform("\nLightSpeed Convertor started...\n");

$commonHelper = Mage::helper('pulliver');
$moduleHelper = Mage::helper('pulliver/LightSpeed');

try {
	$params = isset($argv)
		? Vikont_Pulliver_Helper_Data::getCommandLineParams($argv)
		: new Varien_Object($_GET);

	Mage::register('pulliver_params', $params);

	foreach(Vikont_Pulliver_Helper_LightSpeed::getVendors() as $vendorIndex => $vendor) {
		if($downloadedFileName = $params->getData('file_' . $vendor)) {
			Vikont_Pulliver_Helper_Data::inform(sprintf('Skipped downloading, using local file %s', $downloadedFileName));
			$data = $moduleHelper->decodeFile($downloadedFileName);
		} else {
			$data = $moduleHelper->decodeJson($moduleHelper->downloadVendorRepository($vendor));
		}

		if($data) {
			Vikont_Pulliver_Helper_Data::inform('data decoded successfully for vendor: '.$vendor);
		} else {
			Vikont_Pulliver_Helper_Data::inform('could not decode inventory from vendor: '.$vendor);
			continue;
		}

		// file stuff
		$fileName = $moduleHelper->getLocalFileName('lightspeed-'.$vendor.'.csv');
		$fileHandle = $commonHelper->openFile($fileName);

		fputcsv($fileHandle, array_keys($data[0]));

		foreach($data as $item) {
			fputcsv($fileHandle, $item);
		}

		fclose($fileHandle);

		Vikont_Pulliver_Helper_Data::inform(sprintf('Successfully created file %s having %d lines', $fileName, count($data)));
	}

} catch (Exception $e) {
	Mage::logException($e);
	Vikont_Pulliver_Helper_Data::inform($e->getMessage());
}