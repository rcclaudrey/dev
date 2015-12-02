<?php

class Vikont_Fitment_Helper_Data extends Mage_Core_Helper_Abstract
{
	const ARI_ACTIVITY_ATTRIBUTE_CODE = 'ari_activity_id';
	const TMS_ACTIVITY_ATTRIBUTE_CODE = 'tms_activity_id';
	const ARI_HAS_FITMENT_ATTRIBUTE_CODE = 'has_fitment';
	const ARI_CATEGORY_ATTRIBUTE_CODE = 'ari_category_id';
	const ARI_SUBCATEGORY_ATTRIBUTE_CODE = 'ari_subcategory_id';

	const FITMENT_ID_COOKIE_NAME = 'ari_fitment_id';

	const DEFAULT_TMS_ACTIVITY = 0;

	protected static $isEnabled = null;
	protected static $_productHasAttibutesCache = array();

	protected static $_blockDependency = array(
		'activity' => array('selector', 'filter', 'search', 'toolbar', 'pager', 'list'),
		'selector' => array('filter', 'search', 'toolbar', 'pager', 'list'),
		'filter' => array('filter', 'toolbar', 'pager', 'list'),
		'search' => array('filter', 'toolbar', 'pager', 'list'),
		'toolbar' => array('pager', 'list'),
		'pager' => array('pager', 'list'),
		'list' => array(), // for future use
		'init' => array('activity', 'selector', 'list'),
		'rideRequired' => array('selector', 'filter', 'search', 'toolbar', 'pager', 'list'), //array('selector'),
		'*' => array('activity', 'selector', 'filter', 'search', 'toolbar', 'pager', 'list'),
	);

	protected static $_tmsActivities = array(
		0 => array('ari_activity' => 1, 'name' => 'Street'),
		1 => array('ari_activity' => 2, 'name' => 'Cruiser'),
		2 => array('ari_activity' => 3, 'name' => 'Dirt'),
		3 => array('ari_activity' => 3, 'name' => 'Adventure'),
		4 => array('ari_activity' => 5, 'name' => 'ATV'),
		5 => array('ari_activity' => 5, 'name' => 'UTV'),
		6 => array('ari_activity' => 1, 'name' => 'Scooter'),
		7 => array('ari_activity' => 6, 'name' => 'Snow'),
		8 => array('ari_activity' => 7, 'name' => 'H2O'),
	);

	protected static $_tmsActivityNames = array(
		0 => 'street',
		1 => 'cruiser',
		2 => 'dirt',
		3 => 'adventure',
		4 => 'atv',
		5 => 'utv',
		6 => 'scooter',
		7 => 'snow',
		8 => 'water',
	);

	protected static $_tireshopActivities = array(
		0 => array('ari_activity' => 1, 'name' => 'Street', 'ExtraFilter' => null),
		1 => array('ari_activity' => 2, 'name' => 'Cruiser', 'ExtraFilter' => null),
		2 => array('ari_activity' => 3, 'name' => 'Dirt', 'ExtraFilter' => null),
		3 => array('ari_activity' => 3, 'name' => 'Adventure', 'ExtraFilter' => array('att_58' => 9352)), // Tire Type = Dual Sport
		4 => array('ari_activity' => 5, 'name' => 'ATV', 'ExtraFilter' => null),
		5 => array('ari_activity' => 5, 'name' => 'UTV', 'ExtraFilter' => null),
		6 => array('ari_activity' => 1, 'name' => 'Scooter', 'ExtraFilter' => array('att_58' => 9356)), // Tire Type = Scooter/Moped
	);

	protected static $_categoryDataCache = array();


	public static function isEnabled()
	{
		if(null === self::$isEnabled) {
			self::$isEnabled = (bool) Mage::getStoreConfig('fitment/general/enabled');
		}

		return self::$isEnabled;
	}



	/*
	 * Returns TMS activity
	 * @return int
	 */
	public static function getActivityId()
	{
		$activityId = (int)Mage::registry('current_activity');

		if(null === $activityId) {
			$activityId = (int)Mage::app()->getRequest()->getParam('activity');
		}

		if((null === $activityId)
		&& $currentCategory = Mage::registry('current_category')
		) {
			$activityId = self::getActivityIdFromCategory($currentCategory, 'tms_activity_id');
		}

		return (null !== $activityId) ? $activityId : self::DEFAULT_TMS_ACTIVITY;
	}



	/*
	 * Detects current TMS activity ID by product
	 * @param object $product The product
	 * @return int|false TMS activity ID
	 */
	public static function getActivityIdFromProduct($product)
	{
		foreach($product->getCategoryIds() as $categoryId) {
			if($activityId = self::getActivityIdFromCategory($categoryId, 'tms_activity_id')) {
				return $activityId;
			}
		}
		return false;
	}



	/*
	 * Returns TMS activity from category
	 *
	 * @param int|Mage_Catalog_Model_Category Category
	 * @param string Name of the field to retrieve
	 * @return int TMS Activity ID
	 */
	public static function getActivityIdFromCategory($category, $fieldName = 'tms_activity_id')
	{
		$categoryId = is_object($category) ? $category->getId() : $category;

		if(isset(self::$_categoryDataCache[$categoryId][$fieldName])) {
			return self::$_categoryDataCache[$categoryId][$fieldName];
		}

		if(!is_object($category)) {
			$category = Mage::getModel('catalog/category')->load($category);
		}

		$value = 0;

		foreach(array_reverse($category->getPathIds()) as $pathId) {
			if($categoryId == $pathId) { // preventing re-loading the category passed as function parameter
				$tCategory = $category;
			} else {
				$tCategory = Mage::getModel('catalog/category')->load($pathId);
			}

			$value = $tCategory->getData($fieldName);

			if($tCategory->getLevel() <= 1 ) {
				break;
			}

			if(null !== $value) {
				self::$_categoryDataCache[$pathId] = array($fieldName => $value);
				break;
			}
		}
		return $value;
	}



	public function formatRideName($ride)
	{
		if(isset($ride['Id'])) {
			$rideName = $this->__('%s - %d - %s', $ride['Make']['Name'], $ride['Year'], $ride['Model']['Name']);
		} else {
			$rideName = (isset($ride['id']) && $ride['id'])
				?	(	(isset($ride['name']) && $ride['name'])
						?	$ride['name']
						:	$this->__('%s - %d - %s', $ride['make']['name'], $ride['year'], $ride['model']['name'])
					)
				:	$this->__('Not selected');
		}

		return $rideName;
	}



	public function getDefaultRide($tmsActivityId = null)
	{
		return array(
			'id' => null,
			'name' => $this->__('Not selected'),
			'tms_activity' => $tmsActivityId ? $tmsActivityId : self::DEFAULT_TMS_ACTIVITY,
		);
	}



	public function completeRideInfo($tmsActivity, $fitmentId, $vehicleName = '')
	{
		$ride = array(
			'tms_activity' => $tmsActivity,
			'id' => $fitmentId,
		);

		if(!$vehicleName) {
			$fitmentInfo = Mage::helper('fitment/api')->request('fitment', array('fitment' => $fitmentId));
			if($fitmentInfo) {
				$vehicleName = $this->formatRideName($fitmentInfo);
			}
		}

		$ride['name'] = $vehicleName;

		return $ride;
	}



	protected function _keepRide($ride)
	{
		Mage::getSingleton('core/session')->unsRide();
		Mage::getSingleton('core/session')->setRide($ride);
		setcookie(self::FITMENT_ID_COOKIE_NAME, sprintf('%d_%d', $ride['tms_activity'], $ride['id']), time() + 3600*24*30, '/');
		return $ride;
	}



	public function getCurrentRide($tmsActivityId = null)
	{
		if(!$tmsActivityId) {
			$tmsActivityId = $this->getActivityId();
		}

		$sessionRide = Mage::getSingleton('core/session')->getRide();

		if(	isset($sessionRide['tms_activity'])
		&&	($tmsActivityId == $sessionRide['tms_activity'])
		&&	isset($sessionRide['id'])
		&&	$sessionRide['id']
		) {
			return $sessionRide;
		} else {
			$cookieValue = @$_COOKIE[self::FITMENT_ID_COOKIE_NAME];
			if($cookieValue) {
				$values = explode('_', $cookieValue);

				if(count($values) > 1) {
					list($tmsActivity, $fitmentId) = $values;

					if($tmsActivity == $tmsActivityId) {
						$ride = $this->completeRideInfo($tmsActivity, $fitmentId);
						$this->_keepRide($ride);
						return $ride;
					}
				}
			}
		}
		return $this->getDefaultRide($tmsActivityId);
	}



	public function setCurrentRide($tmsActivityId, $fitmentId, $vehicleName = '')
	{
		if($fitmentId) {
			$ride = $this->completeRideInfo($tmsActivityId, $fitmentId, $vehicleName);
			$this->_keepRide($ride);
			return $ride;
		} else {
			Mage::getSingleton('core/session')->unsRide();
			setcookie(Vikont_Fitment_Helper_Data::FITMENT_ID_COOKIE_NAME, '', time() - 3600, '/');
			return $this->getDefaultRide($tmsActivityId);
		}
	}



	public static function getBlockDependency($blockType)
	{
		return isset(self::$_blockDependency[$blockType])
			?	self::$_blockDependency[$blockType]
			:	array();
	}



	public static function collectCommonParams()
	{
		$result = array(
			'includeFacets' => true,
			'skip' => 0,
			'take' => Vikont_Fitment_Block_Fitment_Pager::getDefaultPageSize(),
			'sort' => Vikont_Fitment_Block_Fitment_Toolbar::getDefaultSort(),
		);
		return $result;
	}



	public static function processImageUrl($url, $dimension=160)
	{
		$url = str_replace('http:', '', $url);

		if(false !== $signPos = strpos($url, '?')) {
			$url = substr($url, 0, $signPos);
		}

		return $url . '?width=' . $dimension;
	}



	public static function sortOptions($options, $fieldName)
	{
		$sort = array();

		foreach($options as $key => $value) {
			$sort[$value[$fieldName]] = $key;
		}

		ksort($sort);

		$result = array();

		foreach($sort as $key => $value) {
			$result[] = $options[$value];
		}

		return $result;
	}



	public static function normalizeCategoryName($value)
	{
		return preg_replace('/[^0-9a-z]/i', '', strtolower($value));
	}



	public function getAriActivities()
	{
		$cachedValue = Mage::getStoreConfig('fitment/cache/activities');

		if($cachedValue) {
			return json_decode($cachedValue, true);
		}

		$data = Mage::helper('fitment/api')
				->preventErrorReporting()
				->request('activities');

		if(!$data) return array(array('Id' => null, 'Name' => '-- Cannot get activity list --')); // life is pain sometimes!

		Mage::getConfig()->saveConfig('fitment/cache/activities', json_encode($data));

		return $data;
	}



	public function getCategories($tmsActivityId = null)
	{
		$tmsActivityId = $tmsActivityId ? $tmsActivityId : self::getActivityId();
		$ariActivityId = self::$_tmsActivities[$tmsActivityId]['ari_activity'];

		$cachedValue = Mage::getStoreConfig('fitment/cache/categories_' . (int)$tmsActivityId);

		if($cachedValue) {
			return json_decode($cachedValue, true);
		}

		$data = Mage::helper('fitment/api')->request('categories', array($ariActivityId));
		if(!$data) return array(); // in case ARI sucked

		Mage::getConfig()->saveConfig('fitment/cache/categories_' . (int)$tmsActivityId, json_encode($data));

		return $data;
	}



	public function getTiresCategoryId($tmsActivityId = null)
	{
		$tmsActivityId = $tmsActivityId ? $tmsActivityId : self::getActivityId();
		$ariActivityId = self::$_tireshopActivities[$tmsActivityId]['ari_activity'];

		$cachedValue = Mage::getStoreConfig('fitment/cache/tires_category_id_' . (int)$tmsActivityId);

		if($cachedValue) {	return $cachedValue;	}

		$data = $this->getCategories($tmsActivityId);
		if(!$data) {	return null;	}	// oh shit!

		// looking for "Tires & Wheels" category to get its ID
		$searchTerm = self::normalizeCategoryName(Mage::getStoreConfig('fitment/tireshop/tires_n_wheels_category_name'));
		$categoryId = false;

		foreach($data as $item) {
			if($searchTerm == self::normalizeCategoryName($item['Name'])) {
				$categoryId = $item['Id'];
				break;
			}
		}

		if($categoryId) {
			Mage::getConfig()->saveConfig('fitment/cache/tires_category_id_' . (int)$tmsActivityId, $categoryId);
			return $categoryId;
		}

		return null;
	}



	public function getTireSizes($tmsActivityId)
	{
		$tireSizes = Mage::getStoreConfig('fitment/cache/tire_sizes_' . (int)$tmsActivityId);
		if($tireSizes) {
			$result = json_decode($tireSizes, true);
			if(is_array($result)) {
				return $result;
			}
		}

		// otherwise, long story begins...
		$categoryId = $this->getTiresCategoryId($tmsActivityId);
		if(!$categoryId) {	return array();	}	// life's sad!

		$options = array(
				'includeFacets' => 'true',
				'categoryID' => $categoryId,
				'take' => 0,
			);
		$options = self::applyExtraFilter($options, $tmsActivityId);

		$ariActivityId = self::$_tireshopActivities[$tmsActivityId]['ari_activity'];
		$wheels = Mage::helper('fitment/api')->request('search', array($ariActivityId), $options);
		if(!$wheels) {	return array();	}	// ARI sucked again

		// now we're looking for "Tire Size" facet
		$facetName = self::normalizeCategoryName(Mage::getStoreConfig('fitment/tireshop/tiresize_attr_name'));
		foreach($wheels['Facets'] as $facetIndex => $facet) {
			if($facetName == self::normalizeCategoryName($facet['Name'])) {
				$result = self::sortOptions($facet['Values'], 'Name');

				Mage::getConfig()->saveConfig('fitment/cache/tiresize_attr_code', $facet['Field']);
				Mage::getConfig()->saveConfig('fitment/cache/tire_sizes_' . (int)$tmsActivityId, json_encode($result));

				return $result;
			}
		}

		return array();
	}



	public function getTireSizeAttributeCode()
	{
		$res = Mage::getStoreConfig('fitment/cache/tiresize_attr_code');

		if(!$res) {
			$this->getTireSizes($this->getActivityId());
		}

		return Mage::getStoreConfig('fitment/cache/tiresize_attr_code');
	}



	public static function getTireshopActivities()
	{
		return self::$_tireshopActivities;
	}



	public static function getTireshopActivity($index, $field = null)
	{
		return isset(self::$_tireshopActivities[$index])
			?	(	$field
					?	self::$_tireshopActivities[$index][$field]
					:	self::$_tireshopActivities[$index]
				)
			:	null;
	}



	public static function applyExtraFilter($to, $activityIndex)
	{
		if(isset(self::$_tireshopActivities[$activityIndex]['ExtraFilter'])
		&& self::$_tireshopActivities[$activityIndex]['ExtraFilter']
		) {
			$to = array_merge($to, self::$_tireshopActivities[$activityIndex]['ExtraFilter']);
		}
		return $to;
	}



	public static function reportError($message)
	{
		$errorId = rand(100, 100000);
		Mage::log('ERROR: #'.$errorId.' '.$message);
//		return sprintf('This operation is currently not available, please try again later. If the problem persists, please contact site administrator. Report #%d', $errorId);
		return sprintf('Fitment currently not available. Report #%d', $errorId);
	}



    public function getRewrittenProductUrl($productId, $categoryId = null, $storeId = null)
    {
        $coreUrl = Mage::getModel('core/url_rewrite');
        $idPath = sprintf('product/%d', $productId);
        if ($categoryId) {
            $idPath = sprintf('%s/%d', $idPath, $categoryId);
        }
//        $coreUrl->setStoreId($storeId);
        $coreUrl->loadByIdPath($idPath);

        return $coreUrl->getRequestPath();
    }



	public function getProductInfo($ariProductId)
	{
		$productInfo = Mage::registry('ari_product_info_' . $ariProductId);

		if(!$productInfo) {
			$productInfo = Mage::helper('fitment/api')
					->preventErrorReporting()
					->request('product', array($ariProductId));

			if(!$productInfo) {
				Mage::log(sprintf('ERROR: Could not retrieve product information, productId=%d', $ariProductId));
				return null;
			}

			Mage::register('ari_product_info_' . $ariProductId, $productInfo);
		}

		return $productInfo;
	}



	public function productHasAttributes($ariProductId)
	{
		$productInfo = $this->getProductInfo($ariProductId);
		return isset($productInfo['HasAttributes']) ? $productInfo['HasAttributes'] : false;
	}



	public function productHasFitments($ariProductId)
	{
		$productInfo = $this->getProductInfo($ariProductId);
		return isset($productInfo['HasFitments']) ? $productInfo['HasFitments'] : false;
	}



	/*
	 * Tries to retrieve TMS configurable product ID from ARI product ID
	 * $param int $ariProductId
	 * #return int|null Product ID to report to frontend
	 */
	public function detectConfigurableProduct($ariProductId)
	{
		$attrInfo = Mage::helper('fitment/api')->request('productattributes', array($ariProductId));

		Mage::register('attrInfo', $attrInfo);

		if(!count($attrInfo)) {
			Mage::log(sprintf('ERROR: Unable to find product: SKU info is empty: ARI product=%d', $ariProductId));
			return null;
		}

		$skus = array();

		foreach($attrInfo[0]['Attributes'] as $attributeValues) { // we need one attribute only as they all contain all SKUs
			foreach($attributeValues['SkuIds'] as $skuId) {
				$skus[$skuId] = 1;
			}
		}

		$productId = Vikont_Fitment_Helper_Db::getTableValue(
				Vikont_Fitment_Helper_Db::getTableName('catalog/product'),
				'entity_id',
				'type_id="configurable" AND sku IN ("' . implode('c","', array_keys($skus)) . 'c")'
			);

		if(!$productId) {
			Mage::log(sprintf('ERROR: Unable to find configurable product: ARI product=%d, SKUs=[%s]', $ariProductId, implode(', ', $skus)));
		}

		return $productId;
	}



	public function detectSimpleProduct($ariProductId, $ariActivityId, $fitmentId = null)
	{/*
		$skuInfo = Mage::helper('fitment/api')
				->preventErrorReporting()
				->request('fitmentnotes',
						array(
							'productID' => $ariProductId,
							'fitmentID' => $fitmentId,
						)
					);/**/
		$skuInfo = Mage::helper('fitment/api')->request(
				'skuinfo',
				array(
					'activityId' => $ariActivityId,
					'productId' => $ariProductId,
				),
				array(
					'fitmentId' => $fitmentId,
				)
			);

		Mage::register('skuInfo', $skuInfo);

		if(!count($skuInfo)) {
			Mage::log(sprintf('ERROR: Unable to find product: SKU info is empty: ARI activity=%d, ARI product=%d,  fitment=%d',
					$ariActivityId, $ariProductId, $fitmentId));
//			Mage::log(sprintf('ERROR: Unable to find product: SKU info is empty: ARI product=%d,  fitment=%d',
//					$ariProductId, $fitmentId));

			return null;
		}

		$skus = array();
		foreach($skuInfo as $skuInfoItem) {
			$skus[] = $skuInfoItem['Id'];
//			$skus[] = $skuInfoItem['SkuId'];
		}

		$productId = Vikont_Fitment_Helper_Db::getTableValue(
				Vikont_Fitment_Helper_Db::getTableName('catalog/product'),
				'entity_id',
				'type_id="simple" AND sku IN ("' . implode('","', $skus) . '")'
			);

		if(!$productId) {
//			Mage::log(sprintf('ERROR: Unable to find a product: ARI product=%d, fitment=%d, ARI product SKU=%d', $ariProductId, $fitmentId, implode(', ', $skus)));
			Mage::log(sprintf('ERROR: Unable to find a product: ARI activity=%d, ARI product=%d, fitment=%d, ARI product SKU=%d', $ariActivityId, $ariProductId, $fitmentId, implode(', ', $skus)));
		}

		return $productId;
	}



	public function detectProductIdInfo($ariProductId, $ariActivityId, $fitmentId = null)
	{
		$result = array(
			'simple' => false,
			'configurable' => false,
		);

		$productId = $this->detectSimpleProduct($ariProductId, $ariActivityId, $fitmentId);
		if($productId) {
			$result['simple'] = $productId;
		}

		if($this->productHasAttributes($ariProductId)) {
			$productId = $this->detectConfigurableProduct($ariProductId);
			if($productId) {
				$result['configurable'] = $productId;
			}
		}

		return $result;
	}



	public static function getTmsActivities()
	{
		return self::$_tmsActivities;
	}



	public static function getTmsActivityNames()
	{
		return self::$_tmsActivityNames;
	}



	public static function getTmsActivity($index, $field = null)
	{
		return isset(self::$_tmsActivities[$index])
			?	(	$field
					?	self::$_tmsActivities[$index][$field]
					:	self::$_tmsActivities[$index]
				)
			:	null;
	}



	/*
	 * Retrieves values for fitment queries: make, year, and model
	 *
	 * @param array $data [activity, subject=[makes, years, models], product]
	 *
	 * @return array
	 */
	public function getFitmentValues($data)
	{
		$mandatoryParams = array();
		$optionalParams = array();

		if(isset($data['product']) && $data['product']) {
			$optionalParams['ProductId'] = $data['product'];
		}

		if(isset($data['sku']) && $data['sku']) {
			$optionalParams['SkuID'] = $data['sku'];
		}

		switch ($data['subject']) {
			case 'models':
				if(isset($data['year']) && $data['year']) {
					$mandatoryParams[2] = $data['year'];
				} else {
					return false;
				}
//				break; // no break here! we're moving forward!

			case 'years':
				if(isset($data['make']) && $data['make']) {
					$mandatoryParams[1] = $data['make'];
				} else {
					return false;
				}
//				break; // no break here! we're moving forward!

			case 'makes':
				if(isset($data['activity']) && $data['activity']) {
					$mandatoryParams[0] = $data['activity']; // this should be ARI activity
				} else {
					return false;
				}
		}

		ksort($mandatoryParams);

		return Mage::helper('fitment/api')->request($data['subject'], $mandatoryParams, $optionalParams);
	}



	/*
	 * Retrieves fitment makes
	 * @param int $ariActivityId ARI activity ID
	 * @return array
	 */
	public function getMakes($ariActivityId)
	{
		$cacheKey = 'fitment/cache/makes_' . (int)$ariActivityId;
		$values = Mage::getStoreConfig($cacheKey);
		if($values) {
			return json_decode($values, true);
		}

		$data = Mage::helper('fitment/api')->request('makes', array($ariActivityId));
		Mage::getConfig()->saveConfig($cacheKey, json_encode($data));

		return $data;
	}

}