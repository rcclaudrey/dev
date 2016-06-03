<?php

include_once '../Vic.php';
require_once 'ARIOEMAPI_Config.php';
require_once 'ARIOEMAPI_Translate.php';
require_once '../lib/Vikont/ARIOEMAPI.php';

$arioemConfig = new ARIOEMAPI_Config();
$arioemConfigData = $arioemConfig->config();

$arioemTranslation = new ARIOEMAPI_Translate();
$arioemConfigData['translate'] = $arioemTranslation->config();

$oem = new Vikont_ARIOEMAPI($arioemConfigData);

try {
	$response = $oem->dispatch($_GET);
} catch (Exception $e) {
	$response = array(
		'error' => true,
		'errorMessage' => $e->getMessage()
	);
}

header('Content-Type: text/json');

echo json_encode($response);

?>