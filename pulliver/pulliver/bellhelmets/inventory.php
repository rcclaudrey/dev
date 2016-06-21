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
Vikont_Pulliver_Helper_Data::setSilentExceptions(false);
Vikont_Pulliver_Helper_Data::inform("\nBell Helmets Connector started...\n");

$commonHelper = Mage::helper('pulliver');
$moduleHelper = Mage::helper('pulliver/BellHelmets');
$skuHelper = Mage::helper('pulliver/Sku');

try {
	$params = isset($argv)
		? Vikont_Pulliver_Helper_Data::getCommandLineParams($argv)
		: new Varien_Object($_GET);

	if($downloadedFileName = $params->getData('file')) {
		Vikont_Pulliver_Helper_Data::inform(sprintf('Skipped downloading, using local file %s', $downloadedFileName));
	} else {
		$downloadedFileName = $moduleHelper->downloadFile();
	}

	$update = $moduleHelper->parseFile($downloadedFileName);
vd($update);
	$outputFileName = $moduleHelper->getLocalFileName('inventory.csv');
	$fileHandle = $commonHelper->openFile($outputFileName);
	$lineCounter = 0;

	foreach($update as $itemNumber => $qty) {
		if($sku = $skuHelper->getSkuByItemNumber('BL', $itemNumber)) {
			fputcsv($fileHandle, array($sku, $qty));
			$lineCounter++;
		}
	}

	fclose($fileHandle);

	Vikont_Pulliver_Helper_Data::inform(sprintf('Successfully created file %s, %d lines processed, %d lines added',
			$outputFileName,
			count($update),
			$lineCounter
		));
} catch (Exception $e) {
	Mage::logException($e);
	Vikont_Pulliver_Helper_Data::inform($e->getMessage());
}