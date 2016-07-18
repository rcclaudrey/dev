<?php

class Vikont_ARIOEM_Helper_Data extends Mage_Core_Helper_Abstract
{
	const BARCODE_CACHE_DIR = '/barcode';
	const BARCODE_CACHE_DISTRI_DEPTH = 2;
	const BARCODE_IMAGE_TYPE = 'png';

	protected static $isEnabled = null;
	protected static $_customerGroup = null;
	protected static $_customerCostPercent = null;
	protected static $_currentBrandName = null;

	protected static $_brandARICode2Name = array(
		'ARC'		=> 'Arctic Cat',
		'BRP'		=> 'Can-Am',
		'HOM'		=> 'Honda',
		'HONPE'		=> 'Honda Power Equipment',
		'KUS'		=> 'Kawasaki',
		'POL'		=> 'Polaris',
		'BRP_SEA'	=> 'Sea-Doo',
		'SUZ'		=> 'Suzuki',
		'SLN'		=> 'Slingshot',
		'VIC'		=> 'Victory',
		'YAM'		=> 'Yamaha',
	);

	protected static $_brandShortcode2Name = array(
		'acat' => 'Arctic Cat',
		'arcticcat' => 'Arctic Cat',
		'canam' => 'Can Am',
		'can-am' => 'Can Am',
		'honda' => 'Honda',
		'hondape' => 'Honda PE',
		'honda-pe' => 'Honda PE',
		'kawasaki' => 'Kawasaki',
		'polarsea-doois' => 'Polaris',
		'seadoo' => 'Sea-Doo',
		'sea-doo' => 'Sea-Doo',
		'slingshot' => 'Slingshot',
		'suzuki' => 'Suzuki',
		'victory' => 'Victory',
		'yamaha' => 'Yamaha',
	);

	protected static $_brandShortcode2ari = array(
		'acat' => 'ARC',
		'arcticcat' => 'ARC',
		'canam' => 'BRP',
		'can-am' => 'BRP',
		'honda' => 'HOM',
		'hondape' => 'HONPE',
		'honda-pe' => 'HONPE',
		'kawasaki' => 'KUS',
		'polaris' => 'POL',
		'seadoo' => 'BRP_SEA',
		'sea-doo' => 'BRP_SEA',
		'slingshot' => 'SLN',
		'suzuki' => 'SUZ',
		'victory' => 'VIC',
		'yamaha' => 'YAM',
	);



	public static function brandARICode2Name($code)
	{
		$code = strtoupper($code);

		return isset(self::$_brandARICode2Name[$code])
			?	self::$_brandARICode2Name[$code]
			:	false;
	}



	public static function brandURLNameToName($urlName)
	{
		return isset(self::$_brandShortcode2Name[$urlName])
			?	self::$_brandShortcode2Name[$urlName]
			:	false;
	}



	protected static function getParsedBrandCodes()
	{
		$result = array();

		$lines = explode(',', Mage::getStoreConfig('arioem/ari/ari_brands_codes'));

		foreach($lines as $line) {
			$line = str_replace(array(' ', ',', "\n", "\r", "\t"), '', $line);
			if(!$line) continue;
			@list($urlCode, $ariCode) = explode('=', $line);
			$result[strtolower($urlCode)] = strtoupper($ariCode);
		}

		return $result;
	}



	public static function brandURLNameToARI($value)
	{
		$brandCode = strtolower($value);

		$parsedCodes = self::getParsedBrandCodes();

		if (isset($parsedCodes[$value])) {
			return $parsedCodes[$value];
		}

		return isset(self::$_brandShortcode2ari[$brandCode])
			?	self::$_brandShortcode2ari[$brandCode]
			:	false;
	}



	public function getCategoryName()
	{
		if($category = Mage::registry('current_category')) {
			return $category->getName();
		} else {
			return ucwords($this->getBrandNameFromUrl());
		}
	}



	public function getCurrentBrandName()
	{
		if(null === self::$_currentBrandName) {
			if($category = Mage::registry('current_category')) {
				self::$_currentBrandName = $category->getUrlKey();
			} else {
				self::$_currentBrandName = $this->getBrandNameFromUrl();
			}
		}
		return self::$_currentBrandName;
	}



	public function getBrandNameFromUrl()
	{
		$brand = Mage::app()->getRequest()->getOriginalPathInfo();
		$brand = substr($brand, strrpos($brand, '/') + 1);
		$brand = substr($brand, 0, strrpos($brand, '.'));

		return $brand;
	}



	public function request($url, $params = array())
	{
		$ch = curl_init();

		if(count($params)) {
			$url .= ((false === strpos($url, '?')) ? '?' : '&' ) . http_build_query($params);
		}

		if(@$_GET['debug'] == 'print') vd($url);
		if(@$_GET['debug'] || Mage::registry('vd')) Mage::log($url);

		curl_setopt_array($ch, array(
			CURLOPT_URL => $url,
			CURLOPT_VERBOSE => 0,
			CURLOPT_RETURNTRANSFER => 1,
//			CURLOPT_SSLVERSION => 3,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_SSL_VERIFYHOST => 2,
		));

		$response = curl_exec($ch);

		if(curl_exec($ch) === false) {
			$error = curl_error($ch);
			curl_close($ch);
			throw new Exception('CURL error: ' . $error);
		}

		curl_close($ch);

		return $response;
	}



	public function composeURL($action)
	{
		return rtrim(Mage::getStoreConfig('arioem/api/stream_endpoint'), '/') . '/' . ltrim($action, '/')
					. '?' . http_build_query(array(
				'arik' => Mage::getStoreConfig('arioem/api/app_key'),
				'ariv' => Mage::getStoreConfig('arioem/api/referer_url'), // 'aril' => ARI_LANGUAGE_CODE,
			));
	}



	public function decodeHTMLResponse($text)
	{
		$translationTable = array(
			'\u003c' => '<',
			'\u003e' => '>',
			'\u0027' => '\'',
			'\u0026' => '&',
			'\r' => chr(13),
			'\n' => chr(10),
		);

		foreach($translationTable as $search => $replace) {
			$text = str_replace($search, $replace, $text);
		}

		return stripslashes($text);
	}



	public function getBrands()
	{
		return self::$_brandARICode2Name;
	}



	public function getAssemblyData($parameters)
	{
		$url = $this->composeURL(Mage::getStoreConfig('arioem/api/parts_assembly'));
		$content = $this->request($url, $parameters);
		$data = json_decode($content, true);

		if(null === $data) {
			throw new Exception(sprintf('Cannot parse a response from %s with parameters=%s, content=%s',
					$url, print_r($parameters, true), $content));
		}

		$data = (array) $data;
		$result = array();

		foreach(@$data['model']['json'] as $item) {
			$result[] = array(
				@$item['attr']['aria'],
				@$item['data'],
				@$item['attr']['slug']
			);
		}

		return $result;
	}



	public static function convertVehicleNameToImageName($value)
	{
		return preg_replace('/[^\w\d\-_]/', '', strtolower($value));
	}



	public function isCustomerWholesale()
	{
		return
//			Mage::helper('core')->isModuleEnabled('Vikont_Wholesale') &&
			Vikont_Wholesale_Helper_Data::isActiveDealer();
	}



	public function getCustomerCostPercent()
	{
		if(null === self::$_customerCostPercent) {
			self::$_customerCostPercent = 0;

			$session = Mage::getSingleton('customer/session');
			if($session->isLoggedIn()) {
				self::$_customerCostPercent = $session->getCustomer()->getData('dealer_cost');
				if(!self::$_customerCostPercent) {
					$groupId = $session->getCustomerGroupId();
					$group = Mage::getModel('customer/group')->load($groupId);
					self::$_customerCostPercent = (float)$group->getCostPercent();
				}
			}
		}
		return self::$_customerCostPercent;
	}



	public static function isEnabled()
	{
		if(null === self::$isEnabled) {
			self::$isEnabled = (bool) Mage::getStoreConfig('arioem/general/enabled');
		}

		return self::$isEnabled;
	}



	public static function reportError($message)
	{
		$errorId = rand(100, 100000);
		Mage::log('ERROR: #'.$errorId.' '.$message);
//		return sprintf('This operation is currently not available, please try again later. If the problem persists, please contact site administrator. Report #%d', $errorId);
		return sprintf('OEM data is not available currently. Report #%d', $errorId);
	}



	// = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = Barcode

	public function getBarcodeImageFile($text, $resultType = 'url')
	{
		$text = strtoupper($text);
		$fileName = preg_replace('/[^0-9A-Z-]/', '', $text);
		$cacheDir = Mage::getModel('core/config')->getVarDir() . self::BARCODE_CACHE_DIR;

		for($distriDepth = 0; $distriDepth < self::BARCODE_CACHE_DISTRI_DEPTH; $distriDepth++) {
			$cacheDir .= (strlen($fileName) > $distriDepth) ? '/' . $fileName[$distriDepth] : '';
		}

		$cachePath = $cacheDir . DS . $fileName . '.' . self::BARCODE_IMAGE_TYPE;

		if(!file_exists($cachePath)) {
			if(!file_exists($cacheDir)) {
				mkdir($cacheDir, 0777, true);
			}

			$config = new Zend_Config(array(
					'barcode' => 'code39',
					'barcodeParams' => array(
						'text' => $text,
					),
					'renderer' => 'image',
					'rendererParams' => array(
						'imageType' => self::BARCODE_IMAGE_TYPE,
					),
				));

			$barcode = Zend_Barcode::factory($config);
			$imageFunction = 'image' . self::BARCODE_IMAGE_TYPE;
			$result = $imageFunction($barcode->draw(), $cachePath);

			if(!$result) return false;
		}

		switch($resultType) {
			case 'url':
				$result = Mage::getUrl('', array('_direct' => str_replace(Mage::getBaseDir() . DS, '', $cachePath)));
				break;

			case 'magepath':
				$result = str_replace(Mage::getBaseDir() . DS, '', $cachePath);
				break;

			case 'fullpath':
			default:
				$result = $cachePath;
		}

		return $result;
	}



	public static function indexArray($arr, $indexField, $unsetKey = true)
	{
		$res = array();

		foreach($arr as $item) {
			$key = $item[$indexField];
			if($unsetKey) {
				unset($item[$indexField]);
			}
			$res[$key] = $item;
		}

		return $res;
	}

}