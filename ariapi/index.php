<?php

require_once '../Vic.php';


class Caller
{
	protected static $_apiActionToRequestPath = array(
		'search' => 'RestAPI/Products/Search',
		'activities' => 'RestAPI/Browse/Activities',
		'makes' => 'RestAPI/Fitment/Makes',
		'years' => 'RestAPI/Fitment/Years',
		'models' => 'RestAPI/Fitment/Models',
		'categories' => 'RestAPI/Browse/Categories',
		'subcategories' => 'RestAPI/Browse/SubCategories',
		'product' => 'RestAPI/Products',
		'productattributes' => 'RestAPI/Products/Attributes',
		'fitment' => 'RestAPI/Fitment',
		'fitmentnotes' => 'RestAPI/Products/FitmentNotes',
//		'' => '',
	);


	public function request($path, $mandatoryParams = array(), $optionalParams = array())
	{
		$baseUrl = 'http://accessorystream.arinet.com/';
		$appKey = 'N8SZjBuVQoU6EhkxtCi2';
		$path = isset(self::$_apiActionToRequestPath[$path]) ? self::$_apiActionToRequestPath[$path] : $path;
		$url = $baseUrl . $path
			. (count($mandatoryParams) ? '/'.implode('/', $mandatoryParams) : '')
			. (count($optionalParams) ? '?'.http_build_query($optionalParams) : '');

		$ch = curl_init();
		curl_setopt_array($ch, array(
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HTTPHEADER => array(
				'applicationKey: '. $appKey,
			)
		));
		$result = curl_exec($ch);
		curl_close($ch);

		return $result ? json_decode($result, true) : $result;
	}

}


$time = microtime(true);


foreach($_POST['params'] as $key => $value) {
	if(!$value) unset($_POST['params'][$key]);
}

foreach($_POST['options'] as $key => $value) {
	if(!$value) unset($_POST['options'][$key]);
}


$caller = new Caller();
$html = vd($caller->request($_POST['requestType'], $_POST['params'], $_POST['options']), true);

$time = round(microtime(true) - $time, 3);

$responseData = array(
	'dump' => $html,
	'time' => $time,
);

echo json_encode($responseData);

?>