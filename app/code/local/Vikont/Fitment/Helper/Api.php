<?php

class Vikont_Fitment_Helper_Api extends Mage_Core_Helper_Abstract
{
	const PREVENT_ERROR_REPORTING_FLAG_NAME = 'prevent_error_reporting';
	const CACHE_PREFIX = 'FITMENT_DATA_';
	const CACHE_DELIMITER = '|';
	const CACHE_ALLOWED_PATHS = ' activities makes years models categories subcategories fitment product ';

	const MAGE_CACHE_GROUP = 'ariapi';
	const MAGE_CACHE_TAG = 'ARI';

	protected $_isMageCacheAllowed = null;

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
		'skuinfo' => 'RestAPI/Skus',
		'skudetails' => 'RestAPI/Skus',
		'reviewlist' => 'RestAPI/Reviews/Product',
		'reviewsummary' => 'RestAPI/Reviews/Summary',
//		'' => '',
		// below are new API calls
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
	);


	public function request($path, $mandatoryParams = array(), $optionalParams = array())
	{
		if($cachedData = $this->checkCache($path, $mandatoryParams, $optionalParams)) {
			return $cachedData;
		}

		$baseUrl = trim(Mage::getStoreConfig('fitment/api/base_url'), ' /') . '/';
		$path = isset(self::$_apiActionToRequestPath[$path]) ? self::$_apiActionToRequestPath[$path] : $path;
		$appKey = Mage::getStoreConfig('fitment/api/app_key');
		$url = $baseUrl . $path
			. (count($mandatoryParams) ? '/'.implode('/', $mandatoryParams) : '')
			. (count($optionalParams) ? '?' . ($this->prepareQuery($optionalParams)) : '');
if(Mage::registry('vd')) Mage::log($url);

		$ch = curl_init();
		curl_setopt_array($ch, array(
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HTTPHEADER => array(
				'applicationKey: '. $appKey,
			)
		));
		$data = curl_exec($ch);
		curl_close($ch);

		$result = json_decode($data, true);

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

		return $result;
	}



	public function prepareQuery($params)
	{
		$result = array();

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



	public function isMageCacheAllowed()
	{
		if (null === $this->_isMageCacheAllowed) {
			$this->_isMageCacheAllowed = (true === Mage::app()->useCache(self::MAGE_CACHE_GROUP));
		}

		return $this->_isMageCacheAllowed;
	}



	public function checkCache($path, $mandatoryParams, $optionalParams)
	{
		if(!$this->isMageCacheAllowed()) {
			return false;
		}

		if($this->_isCacheAllowedForPath($path)) {
			$key = $this->_calculateCacheKey($path, $mandatoryParams, $optionalParams);
			$cache = Mage::app()->getCache();
			return json_decode($cache->load($key), true);
			// replace with: Mage::app()->loadCache($key)
		}
		return false;
	}



	public function saveCache($path, $mandatoryParams, $optionalParams, $data)
	{
		if(!$this->isMageCacheAllowed()) {
			return $this;
		}
//vd($path);
//vd($mandatoryParams);
//vd($optionalParams);
//vd($data);
//vd($this->_isCacheAllowedForPath($path));
		if($this->_isCacheAllowedForPath($path)) {
			$key = $this->_calculateCacheKey($path, $mandatoryParams, $optionalParams);
			$cache = Mage::app()->getCache();
			$cache->save(json_encode($data), $key, array(self::MAGE_CACHE_TAG));
			// replace with: Mage::app()->saveCache()
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