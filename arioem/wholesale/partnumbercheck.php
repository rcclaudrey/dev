<?php

require_once '../../Vic.php';
require_once '../../lib/Vikont/ARIOEM.php';
require_once 'code/common.php';
require_once 'code/Brands.php';

$requester = new Vikont_ARIOEM();

$response = array(
	'errorMessage' => '',
);

try {
	$searchData = $requester->getSearchData(array(
			'key' => 'part',
			'search' => @$_POST['partNumber'],
		));

	$data = array();

	foreach($searchData as $item) {
		if(isset($data[$item[0]])) {
			$data[$item[0]][] = $item[1];
		} else {
			$data[$item[0]] = array($item[1]);
		}
	}

	ob_start();
	include 'template/checked.phtml';
	$response['html'] = ob_get_clean();
	$response['id'] = @$_POST['id'];
} catch (Exception $e) {
	$response['errorMessage'] = $e->getMessage();
}

echo json_encode($response);

?>