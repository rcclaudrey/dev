<?php

return;

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
require 'func.php';

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

//	$am = new AttributeManager();

//	$am->createAttributes($attributes);
//	$am->addAttributeOptions('optical_glass_size', $glassSizes);

	// creating Glasses attribute set
//	$am->addAttributeSet(CPET, GLASSES);

	// ...and groups within it
//	$am->addAttributeGroupToSet(GLASSES, $baseGroups);

	

} catch (Exception $e) {
	Mage::logException($e);
}


?><!doctype html>
<html lang="en-US">
<head>
	<meta charset="utf-8"/>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<meta name="description" content="Default Description"/>
	<meta name="robots" content="INDEX,FOLLOW"/>
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"/>
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0, user-scalable=no"/>
	<title>Attribute management page</title>
	<link  rel="stylesheet" type="text/css"  media="print" href="http://m20.loc/pub/static/frontend/Magento/luma/en_US/css/print.css" />
	<!--<script  type="text/javascript"  src="http://m20.loc/pub/static/frontend/Magento/luma/en_US/requirejs/require.js"></script>-->
</head>
<body>
	<h1>Attribute management</h1>
	<table border="1" cellspacing="0" cellpadding="10px">
		<thead>
			<tr>
				<td>Name</td>
				<td>Code</td>
				<td>Type</td>
			</tr>
		</thead>
		<tbody>
			<?php // foreach( as ) ?>

			<?php // endforeach ?>
			<tr></tr>
		</tbody>
	</table>
	<form name="" action="" method="POST">
		<div>
			<textarea name="attrs"><?php echo $attrCsv ?></textarea>
		</div>
		<div>
			<button type="submit">Create attributes</button>
		</div>
	</div>
</body>
</html>