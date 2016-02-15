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

try {
	$params = isset($argv)
		? Vikont_Pulliver_Helper_Data::getCommandLineParams($argv)
		: new Varien_Object($_GET);

	$sourceFileName = $params->getData('file');

	if($sourceFileName) {
		$sourceFileName = $commonHelper->getImportStorageLocation() . 'kawasaki/source/' . basename($sourceFileName);
		Vikont_Pulliver_Helper_Data::type(sprintf('Using local file %s', $sourceFileName));
	} else {
		Vikont_Pulliver_Helper_Data::type('Please specify a file to convert like ?file={path-to-file}');
		die;
	}

	$update = $moduleHelper->parseFile($sourceFileName);
	$outputFileName = $moduleHelper->getLocalFileName($sourceFileName);
	$fileHandle = $commonHelper->openFile($outputFileName);

	foreach($update as $items) {
		fputcsv($fileHandle, $items);
	}

	fclose($fileHandle);

	Vikont_Pulliver_Helper_Data::inform(sprintf('Successfully created file %s having %d lines', $outputFileName, count($update)));

} catch (Exception $e) {
	Mage::logException($e);
	Vikont_Pulliver_Helper_Data::inform($e->getMessage());
}
