<?php

class Vikont_ARIOEM_Model_Oem_Part
{
	protected $_brand = null;
	protected $_brandCode = null;
	protected $_partNumber = null;
	protected $_partInfo = null;
	protected $_apiPartInfo = null;
	protected $_modelPartInfo = null;
	protected $_models = null;
	protected $_oemAPI = null;
	protected $_assemblyImageUrl = null;

	protected static $_brands = array(
		'acat' => 'Arctic Cat',
		'arcticcat' => 'Arctic Cat',
		'canam' => 'Can Am',
		'can-am' => 'Can Am',
		'honda' => 'Honda',
		'hondape' => 'Honda PE',
		'honda-pe' => 'Honda PE',
		'kawasaki' => 'Kawasaki',
		'polaris' => 'Polaris',
		'sea-doo' => 'Sea-Doo',
		'seadoo' => 'Sea-Doo',
		'slingshot' => 'Slingshot',
		'suzuki' => 'Suzuki',
		'victory' => 'Victory',
		'yamaha' => 'Yamaha',
	);

	protected static $_brands2shortcode = array(
		'acat' => 'ARC',
		'arcticcat' => 'ARC',
		'canam' => 'BRP',
		'can-am' => 'BRP',
		'honda' => 'HOM',
		'hondape' => 'HONPE',
		'honda-pe' => 'HONPE',
		'kawasaki' => 'KUS',
		'polaris' => 'POL',
		'sea-doo' => 'BRP_SEA',
		'seadoo' => 'BRP_SEA',
		'slingshot' => 'SLN',
		'suzuki' => 'SUZ',
		'victory' => 'VIC',
		'yamaha' => 'YAM',
	);


//	public function Vikont_ARIOEM_Model_Oem_Part()
//	{
//	}



	public function getBrand()
	{
		if(null === $this->_brand) {
			$brand = strtolower(Mage::registry('oem_brand'));
			if(!$brand) {
				$brand = Mage::app()->getRequest()->getParam('brand');
			}
			$this->_brand = $brand;
			$this->_brandCode = Vikont_ARIOEM_Helper_Data::brandName2Code($this->_brand);
		}

		return $this->_brand;
	}



	public function setBrand($value)
	{
		$this->_brand = $value;
		return $this;
	}



	public function getBrandName()
	{
		$this->getBrand();

		return isset(self::$_brands[$this->_brand])
			?	self::$_brands[$this->_brand]
			:	$this->_brand;
	}



	public function getBrandCode()
	{
		$this->getBrand();
		return $this->_brandCode;
	}



	public function getPartNumber()
	{
		if(null === $this->_partNumber) {
			$partNumber = Mage::registry('oem_part_number');
			if(!$partNumber) {
				$partNumber = Mage::app()->getRequest()->getParam('partNumber');
			}
			$this->_partNumber = strtoupper(str_replace(array(':', '/', '\\', '"', '\''), '', $partNumber));
		}

		return $this->_partNumber;
	}



	public function setPartNumber($value)
	{
		$this->_partNumber = $value;
		return $this;
	}



	public function getAPIPartInfo()
	{
		if (null === $this->_apiPartInfo) {
			$response = Mage::helper('arioem/api')->request('SearchParts', array(
					'brandCode' => $this->getBrandCode(),
					'partNumber' => $this->getPartNumber(),
				), array());

			if ($response && isset($response['Data']['Results'][0])) {
				$this->_apiPartInfo = $response['Data']['Results'][0];
			}
		}
		return $this->_apiPartInfo;
	}



	public function getPartInfo()
	{
		if (null === $this->_partInfo) {
			$this->getAPIPartInfo();

			$this->_partInfo = array(
				'name' => $this->_apiPartInfo['Description'],
				'part_id' => $this->_apiPartInfo['PartId'],
				'superseded' => $this->_apiPartInfo['IsSuperseded'],
				'nla' => $this->_apiPartInfo['NLA'],
				'has_models' => $this->_apiPartInfo['HasModels'],
			);

			$dbData = Mage::helper('arioem/OEM')->getOEMData($this->getBrandCode(), $this->getPartNumber());

			if ($dbData && $dbData['available']) {
				$this->_partInfo = array_merge($this->_partInfo, array(
					'available' => true,
//					'id' => $dbData['id'],
					'supplier_code' => $dbData['supplier_code'],
					'name' => $dbData['part_name'], // overriding the name in case TMS corrected that
					'uom' => $dbData['uom'],

					'cost' => $dbData['cost'],
					'msrp' => $dbData['msrp'],
					'price' => $dbData['price'],
					'hide_price' => $dbData['hide_price'],

					'inv_local' => $dbData['inv_local'],
					'inv_wh' => $dbData['inv_wh'],

					'length' => $dbData['dim_length'],
					'width' => $dbData['dim_width'],
					'height' => $dbData['dim_height'],
					'weight' => $dbData['weight'],
					'oversized' => $dbData['oversized'],

					'image_url' => $dbData['image_url'],
				));
			} else {
				$this->_partInfo['available'] = false;
			}
		}

		return $this->_partInfo;
	}



	public function isAvailable()
	{
		$this->getPartInfo();
		return $this->_partInfo['available'];
	}



	public function getName()
	{
		$this->getPartInfo();
		return $this->_partInfo['name'];
	}



	public function getPrice($formatted = false)
	{
		$this->getPartInfo();
		return $formatted
			?	Mage::helper('core')->formatPrice($this->_partInfo['price'], false)
			:	$this->_partInfo['price'];
	}



	public function getAPI()
	{
		if (null === $this->_oemAPI) {
			$arioemConfig = array();
			require_once MAGENTO_ROOT . '/arioem/creds.php';
			require_once MAGENTO_ROOT . '/arioem/translate.php';
			$arioemConfig['SITE_ROOT'] = MAGENTO_ROOT;

			$this->_oemAPI = new Vikont_ARIOEMAPI($arioemConfig);
		}
		return $this->_oemAPI;
	}



	public function requestAPI($params)
	{
		try {
			$response = $this->getAPI()->dispatch($params);
		} catch (Exception $e) {
			Mage::logException($e);
			return false;
		}

		return $response;
	}



	public function getModels()
	{
		if(null === $this->_models) {
			$response = $this->requestAPI(array(
				'action' => 'part-models',
				'brandCode' => $this->getBrandCode(),
				'sku' => $this->getPartNumber(),
				'page' => 1,
				'pageSize' => 100,
			));

			if ($response) {
				$this->_models = $response['res'];
			}
		}

		return $this->_models;
	}


	public static function getBrandNameByShortname($shortname)
	{
		return isset(self::$_brands[$shortname])
			?	self::$_brands[$shortname]
			:	$shortname;
	}



	public function getAssemblyImageUrl($width = 500)
	{
		if(null === $this->_assemblyImageUrl) {
			if ($this->_partInfo['image_url']) {
				$this->_assemblyImageUrl = $this->_partInfo['image_url'];
			} else {
				$models = $this->getModels();
				if(!count($models)) return false;

				$response = $this->requestAPI(array(
					'action' => 'part-model-assemblies',
					'brandCode' => $this->getBrandCode(),
					'modelId' => $models[0]['id'],
					'sku' => $this->getPartNumber(),
				));

				if ($response) {
					try {
						$this->_assemblyImageUrl = $this->getAPI()
	//						->setDebugMode(true)
							->processImage(array(
									'brandCode' => $this->getBrandCode(),
									'parentId' => $models[0]['id'],
									'assemblyId' => $response['res'][0]['id'],
									'width' => $width,
								));
					} catch (Exception $e) {
						Mage::logException($e);
						return false;
					}
				}

				Mage::helper('arioem/OEM')->saveImageUrl($this->_partInfo['id'], $this->_assemblyImageUrl);
				$this->_partInfo['image_url'] = $this->_assemblyImageUrl;
			}
		}

		return $this->_assemblyImageUrl;
	}

}