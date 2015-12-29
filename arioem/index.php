<?php

require_once '../Vic.php';
require_once '../lib/Vikont/ARIOEM.php';

$oem = new Vikont_ARIOEM();

try {
	$response = $oem->dispatch();
} catch (Exception $e) {
	$response = array(
		'error' => true,
		'error_message' => $e->getMessage()
	);
}

echo json_encode($response);

?>