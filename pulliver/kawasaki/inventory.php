<?php

ini_set('memory_limit', '2048M');
ini_set('max_execution_time', '600');
set_time_limit(0);

$mageRoot = dirname(dirname(getcwd()));

require $mageRoot . '/app/Mage.php';
Mage::app('admin')->setUseSessionInUrl(false);
umask(0);

require_once $mageRoot . '/Vic.php';

// = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =

Vikont_Pulliver_Model_Log::setLogFileName('pulliver.log');
Vikont_Pulliver_Helper_Data::setSilentExceptions(false);
Vikont_Pulliver_Helper_Data::type("\nKawasaki Converter started...\n");

$commonHelper = Mage::helper('pulliver');
$moduleHelper = Mage::helper('pulliver/Kawasaki');
$skuHelper = Mage::helper('pulliver/Sku');

try {
	$params = isset($argv)
		? Vikont_Pulliver_Helper_Data::getCommandLineParams($argv)
		: new Varien_Object($_GET);

	if($downloadedFileName = $params->getData('file')) {
		Vikont_Pulliver_Helper_Data::type(sprintf('Using local file %s', $downloadedFileName));
	} else {
//		$downloadedFileName = $moduleHelper->downloadFile();
		Vikont_Pulliver_Helper_Data::type('No file to convert');
		die;
	}

	$update = $moduleHelper->parseFile($downloadedFileName);
	$outputFileName = $moduleHelper->getLocalFileName($downloadedFileName);
	$fileHandle = $commonHelper->openFile($outputFileName);
	$lineCounter = 0;

	foreach($update as $item) {
		if($sku = $skuHelper->getSkuByItemNumber('KA', $itemNumber)) {
			fputcsv($fileHandle, array($sku, $item));
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
