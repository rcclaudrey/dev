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

$commonHelper = Mage::helper('pulliver');
$moduleHelper = Mage::helper('pulliver/LightSpeed');
$skuHelper = Mage::helper('pulliver/Sku');

//vd(Vikont_Pulliver_Helper_Sku::getSkuByItemNumber('punlim', '4010-0075'));
vd(Vikont_Pulliver_Helper_Sku::getSkuByItemNumber('FX', '01179-001-S'));
