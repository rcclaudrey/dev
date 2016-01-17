<?php

class Vikont_ARIOEM_Helper_Api extends Mage_Core_Helper_Abstract
{
	const PREVENT_ERROR_REPORTING_FLAG_NAME = 'prevent_error_reporting';
	const CACHE_PREFIX = 'ARIOEM_DATA_';
	const CACHE_DELIMITER = '|';
	const CACHE_ALLOWED_PATHS = '  ';

	const MAGE_CACHE_GROUP = 'ariapi';
	const MAGE_CACHE_TAG = 'ari';

	const ARI_API_RETRY_MAX_COUNT = 10;
	const ARI_API_RETRY_TIME = 100;

	const ARI_LANGUAGE_CODE_PARAM_NAME = 'aril';
	const ARI_LANGUAGE_CODE_PARAM_VALUE = 'en-US';

	protected $_settingsPath = 'api';

	protected $_isMageCacheAllowed = null;

	protected static $_apiActionToRequestPath = array(
		'NodeChildren' => 'RestAPI/NodeChildren',
		'SearchModelAssemblies' => 'RestAPI/SearchModelAssemblies',
		'ModelAutoComplete' => 'RestAPI/ModelAutoComplete',
		'SearchModel' => 'RestAPI/SearchModel',
		'SearchPartModels' => 'RestAPI/SearchPartModels',
		'PartAutoComplete' => 'RestAPI/PartAutoComplete',
		'SearchParts' => 'RestAPI/SearchParts',
		'SearchPartsWithinModel' => 'RestAPI/SearchPartsWithinModel',
		'SearchPartModelsFiltered' => 'RestAPI/SearchPartModelsFiltered',
		'SearchPartModelAssemblies' => 'RestAPI/SearchPartModelAssemblies',
		'AssemblyInfo' => 'RestAPI/AssemblyInfo',
		'AssemblyInfoNoHotSpot' => 'RestAPI/AssemblyInfoNoHotSpot',
		'AssemblyImage' => 'RestAPI/AssemblyImage',
		'hotspots' => 'RestAPI/hotspots',
		'partinfo' => 'RestAPI/partinfo',

//		'' => 'RestAPI/',
		// these is not needed actually as the path can be passed right from the calling function
//		'search' => 'Search',
//		'partModels' => 'Search/GetModelSearchModelsForPrompt',
	);


	public function setApiMode($mode = 'check')
	{
		$this->_settingsPath = $mode;

		return $this;
	}



	public function request($path, $mandatoryParams = array(), $optionalParams = array())
	{
		if($cachedData = $this->checkCache($path, $mandatoryParams, $optionalParams)) {
			return $cachedData;
		}

		if('APIEndPoint' == $path) {
			$url = trim(Mage::getStoreConfig('arioem/' . $this->_settingsPath . '/api_endpoint_url'));
		} else {
			$baseUrl = trim(Mage::getStoreConfig('arioem/' . $this->_settingsPath . '/stream_endpoint'), ' /') . '/';
			$path = isset(self::$_apiActionToRequestPath[$path]) ? self::$_apiActionToRequestPath[$path] : $path;
			$url = $baseUrl . $path
				. (count($mandatoryParams) ? '/'.implode('/', $mandatoryParams) : '')
				. (count($optionalParams) ? '?' . ($this->prepareQuery($optionalParams)) : '');

			if(@$_GET['debug'] == 'print') vd($url);
			if(@$_GET['debug'] || Mage::registry('vd')) Mage::log($url);
		}

		$ch = curl_init();
		curl_setopt_array($ch, array(
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HTTPHEADER => array(
				'applicationkey: '. Mage::getStoreConfig('arioem/' . $this->_settingsPath . '/app_key'),
			)
		));

		$error = false;
		$contentType = '';

		for($retry = 1; $retry < self::ARI_API_RETRY_MAX_COUNT; $retry++) {
			if($error) {
				usleep(self::ARI_API_RETRY_TIME);
			}

			$response = curl_exec($ch);

			if(@$_GET['debug'] == 'print') vd($response);
			if(@$_GET['debug'] || Mage::registry('vd')) Mage::log($response);

			if(false !== $response) {
				break;
			} else {
				$error = curl_error($ch);
			}
		}

		if(false !== $error) {
			throw new Exception('CURL error: ' . $error);
//			return null;
		} else {
			$contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
		}

		curl_close($ch);

		if('APIEndPoint' == $path) {

			Mage::getConfig()->saveConfig('arioem/' . $this->_settingsPath . '/stream_endpoint', $response);
			return $response;
		} elseif(strtolower($contentType) == 'image/gif') {
			return array(
				'responseType' => 'image',
				'image' => $response,
			);
		} else {
			$result = json_decode($response, true);

			if(isset($result['Message'])) {
				$errorMessage = sprintf('Error getting remote data for %s, URL: %s, Message: %s, Message Detail: %s',
						$path,
						$url,
						$result['Message'],
						isset($result['MessageDetail']) ? $result['MessageDetail'] : ''
					);

				if(Mage::registry(self::PREVENT_ERROR_REPORTING_FLAG_NAME)) {
					Mage::log($errorMessage);
				} else {
					throw new Exception($errorMessage);
				}

				return null;
			}

			$this->saveCache($path, $mandatoryParams, $optionalParams, $result);
		}

		return $result;
	}



	public function prepareQuery($params)
	{
		$result = array();

		if(!isset($params[self::ARI_LANGUAGE_CODE_PARAM_NAME])) {
			$params[self::ARI_LANGUAGE_CODE_PARAM_NAME] = self::ARI_LANGUAGE_CODE_PARAM_VALUE;
		}

		foreach($params as $key => $value) {
			if(is_array($value)) {
				foreach($value as $v) {
					$result[] = urlencode($key) . '=' . urlencode($v);
				}
			} else if( (null !== $value) && (false !== $value) && ('' !== $value) ) {
				$result[] = urlencode($key) . '=' . urlencode($value);
			}
		}

		return implode('&', $result);
	}



	public function isMageCacheAllowed()
	{
		if (null === $this->_isMageCacheAllowed) {
			$this->_isMageCacheAllowed = (true === Mage::app()->useCache(self::MAGE_CACHE_GROUP));
		}

		return $this->_isMageCacheAllowed;
	}



	protected function _isCacheAllowedForPath($path)
	{
		$path = strtolower($path);

		if (false === strpos($path, '/')) {
			return (false !== strpos(self::CACHE_ALLOWED_PATHS, ' ' . $path . ' '));
		} else {
			return (false !== strpos(self::CACHE_ALLOWED_PATHS, ' ' . substr($path, strrpos($path, '/') + 1, 100) . ' '));
		}
	}



	protected function _calculateCacheKey($path, $mandatoryParams, $optionalParams)
	{
		ksort($optionalParams);
		$key = $path . self::CACHE_DELIMITER . json_encode(array_values($mandatoryParams)) . self::CACHE_DELIMITER . json_encode($optionalParams);
		return self::CACHE_PREFIX . md5($key);
	}



	public function checkCache($path, $mandatoryParams, $optionalParams)
	{
		if(!$this->isMageCacheAllowed()) {
			return false;
		}

		if($this->_settingsPath == 'check') {
			return false;
		}

		if($this->_isCacheAllowedForPath($path)) {
			$key = $this->_calculateCacheKey($path, $mandatoryParams, $optionalParams);
			$cache = Mage::app()->getCache();
			return json_decode($cache->load($key), true);
		}
		return false;
	}



	public function saveCache($path, $mandatoryParams, $optionalParams, $data)
	{
		if(!$this->isMageCacheAllowed()) {
			return $this;
		}

		if($this->_settingsPath == 'check') {
			return false;
		}

		if($this->_isCacheAllowedForPath($path)) {
			$key = $this->_calculateCacheKey($path, $mandatoryParams, $optionalParams);
			$cache = Mage::app()->getCache();
			$cache->save(json_encode($data), $key, array(self::MAGE_CACHE_TAG));
		}
		return $this;
	}



	public function preventErrorReporting($desiredState = true)
	{
		$value = Mage::registry(self::PREVENT_ERROR_REPORTING_FLAG_NAME);

		if($desiredState != $value) {
			if(null === $value) {
				Mage::unregister(self::PREVENT_ERROR_REPORTING_FLAG_NAME);
			}
			Mage::register(self::PREVENT_ERROR_REPORTING_FLAG_NAME, $desiredState);
		}

		return $this;
	}

}