<?php

class Vikont_ARIOEM_Helper_Data extends Mage_Core_Helper_Abstract
{
	protected static $isEnabled = null;
	protected static $_brands = null;
	protected static $_customerGroup = null;
	protected static $_currentBrandName = null;


	public function brandName2Code($brandName)
	{
		$brandName = strtolower($brandName);
		$lines = explode(',', Mage::getStoreConfig('arioem/ari/ari_brands_codes'));

		foreach($lines as $line) {
			$line = str_replace(array(',', "\n", "\r", "\t"), '', $line);
			if(!$line) continue;
			@list($brand, $code) = explode('=', $line);
			if(strtolower(trim($brand)) == $brandName) {
				return trim($code);
			}
		}
		return false;
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
		if(!self::$_brands) {
			$content = $this->request($this->composeURL(Mage::getStoreConfig('arioem/api/parts_manufacturer')));
			$content = trim($content, 'document.write(\');');
			$content = $this->decodeHTMLResponse($content);

			$dom = new DOMDocument;
			$dom->loadHTML($content);

			$select = $dom->getElementById('ari_brands');
			if(!$select) {
				throw new Exception('No #ari_brands element in the response');
			}

			self::$_brands = array();

			foreach($select->childNodes as $itemIndex => $node) {
				if($itemIndex) {
					self::$_brands[$node->attributes->getNamedItem('value')->value] = $node->textContent; /*
					$result[] = array(
						$node->attributes->getNamedItem('value')->value,
						$node->textContent
					); /**/
				}
			}
		}

		return self::$_brands;
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



	public function getCustomerCostPercent()
	{
		$session = Mage::getSingleton('customer/session');

		$result = $session->isLoggedIn()
			?	$session->getCustomer()->getData('dealer_cost')
			:	null;

		if(!$result) {
			$groupId = $session->getCustomerGroupId();
			$group = Mage::getModel('customer/group')->load($groupId);
			$result = (float)$group->getCostPercent();
		}

		return $result;
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
	const BARCODE_CACHE_DIR = '/barcode';

	public function getBarcodeImageFile($text)
	{
		$fileName = str_replace(array('/', '\\', '\'', '"', '..'), '', $text);
		$cacheDir = Mage::getModel('core/config')->getVarDir() . self::BARCODE_CACHE_DIR; // ??? spreading to a/b/abcde.png
		$cacheDir .= strlen($fileName) ? ('/' . $fileName[0]) : '';
		$cacheDir .= (strlen($fileName) > 1 ) ? '/' . $fileName[1] : '';
//		$cacheDir .= strlen($fileName) ? ('/' . $fileName[0] . (strlen($fileName) > 1 ) ? '/' . $fileName[1] : '') : '';
        $cachePath = $cacheDir . '/' . $fileName . '.png';
vd($text);
vd($cachePath);

		if(file_exists($cachePath)) {
			return $cachePath;
		}

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
					'imageType' => 'png'
				),
		));

		$imageResource = Zend_Barcode::factory($config)->draw();

		if ($f = fopen($cachePath, 'w')) {
            fwrite($f, $imageResource);
            fclose($f);

			return $cachePath;
        } else {
			Mage::log("Could not create $cachePath");
		}

		return false;
	}
}