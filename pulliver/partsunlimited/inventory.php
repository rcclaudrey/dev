<?php

require_once '../../Vic.php';

ini_set('memory_limit', '2048M');
ini_set('max_execution_time', '600');
set_time_limit(0);

$mageRoot = dirname(dirname(getcwd()));

require $mageRoot . '/app/Mage.php';
Mage::app('admin')->setUseSessionInUrl(false);
umask(0);
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

// = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =

Vikont_Pulliver_Model_Log::setLogFileName('pulliver.log');
Vikont_Pulliver_Helper_Data::setSilentExceptions(false);
Vikont_Pulliver_Helper_Data::inform("\nParts Unlimited Connector started...\n");

$commonHelper = Mage::helper('pulliver');
$moduleHelper = Mage::helper('pulliver/PartsUnlimited');
$skuHelper = Mage::helper('pulliver/Sku');

try {
	$params = isset($argv)
		? Vikont_Pulliver_Helper_Data::getCommandLineParams($argv)
		: new Varien_Object($_GET);

	Mage::register('pulliver_params', $params);

	$inventoryFileName = $params->getData('use_local_file');
	$downloadedFileName = $params->getData('use_downloaded_file');

	if(!$inventoryFileName) {
		if($downloadedFileName) {
			Vikont_Pulliver_Helper_Data::inform(sprintf('Skipped downloading, using local file %s', $downloadedFileName));
			if(!file_exists($downloadedFileName)) {
				Vikont_Pulliver_Helper_Data::throwException(sprintf('File %s does not exist', $downloadedFileName));
			}
		} else {
			$downloadedFileName = $moduleHelper->downloadFile();
		}

		$pathInfo = pathinfo($downloadedFileName);
		$dirName = dirname($downloadedFileName) . '/' . $pathInfo['filename'];

		// wiping out the directory assuming it has no directories (but even if it does, who cares?)
		if(file_exists($dirName)) {
			$files = Vikont_Pulliver_Helper_Data::getDirectoryListing($dirName);
			foreach($files as $file => $dummy) {
				@unlink($file);
			}
		} else {
			mkdir($dirName, 0755, true);
		}

		$unzippingResult = Vikont_Pulliver_Helper_Data::unZip($downloadedFileName, $dirName);
		if(true !== $unzippingResult) {
			Vikont_Pulliver_Helper_Data::throwException(sprintf('Error unzipping file %s to %s, result: %d', $downloadedFileName, $dirName, $unzippingResult));
		}

		$files = Vikont_Pulliver_Helper_Data::getDirectoryListing($dirName);

		if(isset($files['PriceFile_system_errors.txt'])) {
			Vikont_Pulliver_Helper_Data::throwException(sprintf(
					'Error getting remote inventory: %s',
					file_get_contents($dirName . '/PriceFile_system_errors.txt')
			));
		}

		$priceFileName = 'BasePriceFile.csv';

		if(isset($files[$priceFileName])) {
			$inventoryFileName = $dirName . '/' . $priceFileName;
		} else {
			Vikont_Pulliver_Helper_Data::throwException(sprintf(
					'No %s file found in the downloaded archive %s at %s',
					$priceFileName,
					$downloadedFileName,
					$dirName
			));
		}
	} else {
		Vikont_Pulliver_Helper_Data::inform(sprintf('Skipped downloading and extracting, using local file %s', $inventoryFileName));
	}

	$update = $moduleHelper->parseFile($inventoryFileName);

	if($dumpParsedFileName = $params->getData('dump_parsed_file')) {
		$dumpParsedFileName = $moduleHelper->getLocalFileName($dumpParsedFileName);
		$fHandle = $commonHelper->openFile($dumpParsedFileName);
		foreach($update as $qtys) {
			fputcsv($fHandle, $qtys);
		}
		fclose($fHandle);
		Vikont_Pulliver_Helper_Data::inform(sprintf('Successfully created a dump of parsed file %s',
			$dumpParsedFileName
		));
	}

	$outputFileName = $moduleHelper->getLocalFileName('inventory.csv');

	$fileHandle = $commonHelper->openFile($outputFileName);
	$lineCounter = 0;

	foreach($update as $qtys) {
		if($sku = Vikont_Pulliver_Helper_Sku::getSkuByItemNumber('PU', $qtys[0])) {
			$qtys[0] = $sku;
			fputcsv($fileHandle, $qtys);
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