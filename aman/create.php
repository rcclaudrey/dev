<?php

require_once '../Vic.php';

//ini_set('memory_limit', '2048M');
//ini_set('max_execution_time', '600');
//set_time_limit(0);

$mageRoot = dirname(getcwd());

require $mageRoot . '/app/Mage.php';
Mage::app('admin')->setUseSessionInUrl(false);
umask(0);
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

// = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =
/*
Vikont_Pulliver_Model_Log::setLogFileName('pulliver.log');
Vikont_Pulliver_Helper_Data::setSilentExceptions(false);
Vikont_Pulliver_Helper_Data::inform("\nParts Unlimited Connector started...\n");

$commonHelper = Mage::helper('pulliver');
$moduleHelper = Mage::helper('pulliver/PartsUnlimited');
$skuHelper = Mage::helper('pulliver/Sku');
/**/


include 'defs.php';
require 'AttributeManager.php';

//switch

try {/*
	$params = isset($argv)
		? Vikont_Pulliver_Helper_Data::getCommandLineParams($argv)
		: new Varien_Object($_GET);

	Mage::register('pulliver_params', $params);
	$downloadedFileName = $params->getData('use_downloaded_file');


	Vikont_Pulliver_Helper_Data::inform(sprintf('Successfully created file %s, %d lines processed, %d lines added',
			$outputFileName,
			count($update),
			$lineCounter
		));
/**/

	$am = new AttributeManager();

	// first we need to create the attributes themselves:
	$am->createAttributes($attributes);
	// ...and their options as well, if needed:
//	$am->addAttributeOptions('attr_code', $attrOptionValues);


	// now, for each attribute set, we need to:
	// create the attribute set:
//	$am->addAttributeSet(CPET, 'attr set name1');

	// add tabbed attr groups to that set:
//	$am->addAttributeGroupToSet('attr set name', $attributeTabs);
//	addAttributeGroupToSet('attr set name1', $baseTabs);
//	addAttributeGroupToSet('attr set name1', $commonTabs);
//	addAttributeGroupToSet('attr set name1', $customTabs1);
	// this should be repeated for each attribute set 
	
	
} catch (Exception $e) {
	Mage::logException($e);
}
