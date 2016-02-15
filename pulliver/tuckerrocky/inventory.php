<?php

ini_set('memory_limit', '2048M');
ini_set('max_execution_time', '600');
set_time_limit(0);

$mageRoot = dirname(dirname(getcwd()));

require $mageRoot . '/app/Mage.php';
Mage::app('admin')->setUseSessionInUrl(false);
umask(0);

// = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =

Vikont_Pulliver_Model_Log::setLogFileName('pulliver.log');
Vikont_Pulliver_Helper_Data::setSilentExceptions(false);
Vikont_Pulliver_Helper_Data::inform("\nTuckerRocky Connector started...\n");

$commonHelper = Mage::helper('pulliver');
$moduleHelper = Mage::helper('pulliver/TuckerRocky');
$skuHelper = Mage::helper('pulliver/Sku');

try {
	$params = isset($argv)
		? Vikont_Pulliver_Helper_Data::getCommandLineParams($argv)
		: new Varien_Object($_GET);

	$inventoryType = $params->getData('type');
	$inventoryType = ($inventoryType == 'master') ? $inventoryType : 'inventory';
	Vikont_Pulliver_Helper_Data::inform(sprintf('Inventory type is: %s', $inventoryType));

	if(!$downloadedFileName = $params->getData('file')) {
		$downloadedFileName = $moduleHelper->downloadFile($inventoryType);
	} else {
		Vikont_Pulliver_Helper_Data::inform(sprintf('Skipped downloading, using local file %s', $downloadedFileName));
	}

	$update = $moduleHelper->parseFile($downloadedFileName, $inventoryType);
	$outputFileName = $moduleHelper->getLocalFileName($moduleHelper->getRemoteFileName($inventoryType) . '.csv');
	$fileHandle = $commonHelper->openFile($outputFileName);
	$lineCounter = 0;

	if($moduleHelper->needsConversion($inventoryType)) {
		foreach($update as $item) {
			fwrite($fileHandle, implode(',', Vikont_Pulliver_Helper_TuckerRocky::adaptVendorDataForImport($item))."\n");
			$lineCounter++;
		}
	} else {
		foreach($update as $item) {
			if($sku = $skuHelper->getSkuByItemNumber('TR', $item['sku'])) {
				$item['sku'] = $sku;
				fputcsv($fileHandle, $item);
				$lineCounter++;
			}
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