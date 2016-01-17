<?php

class Vikont_Fitment_Block_Fitment_View extends Mage_Core_Block_Template
{
	protected static $_ariParams = null;
	protected $_productInfo = null;
	protected $_productList = null;


	protected function _construct()
	{
		parent::_construct();
		self::$_ariParams = Mage::registry('ari_params');
		$this->setTemplate('vk_fitment/fitment/view.phtml');
	}



	public function getProductInfo($ariProductId)
	{
		if(!$this->_productInfo) {
			$this->_productInfo = Mage::helper('fitment/api')
					->preventErrorReporting()
					->request('product', array($ariProductId));
		}
		return $this->_productInfo;
	}



	public function getProducts()
	{
		if(!$this->_productList) {
			$this->_productList = array();

			try {
				$ariProductId = $this->getAriProductId();
				$fitmentId = $this->getFitmentId();

				$fitmentNotes = Mage::helper('fitment/api')
						->preventErrorReporting()
						->request('fitmentnotes',
								array(
									'productID' => $ariProductId,
									'fitmentID' => $fitmentId,
								)
							);

				if(!$fitmentNotes) {
					Mage::log(sprintf('Unable to get fitment notes from ARI API: ARI product ID = %d, ARI fitment ID = %d', $ariProductId, $fitmentId));
				}

				$skuInfo = Mage::registry('skuInfo');
				if(!$skuInfo) {
					throw new Exception('Unable to find "skuInfo" in Mage registry');
				}

				$fitmentItems = array();

				foreach($skuInfo as $item) {
					$sku = (string)$item['Id'];
					$fitmentItems[$sku] = $item;
				}

				foreach($fitmentNotes as $item) {
					$sku = (string)$item['SkuId'];

					if(isset($fitmentItems[$sku])) {
						$fitmentItems[$sku]['Applications'] = $item['Applications'];
						$fitmentItems[$sku]['Note'] = $item['Note'];
					}
				}

				foreach($fitmentItems as &$item) {
					if(!isset($item['Applications'])) {
						$item['Applications'] = array();
						$item['Note'] = '';
					}
				}
				unset($item);

				$collection = Mage::getResourceModel('catalog/product_collection')
						->addAttributeToSelect('name')
						->addFinalPrice()
						->addFieldToFilter('sku', array('in' => array_keys($fitmentItems)));

				foreach($collection as $product) {
					$sku = $product->getSku();
					if(isset($fitmentItems[$sku])) {
						$this->_productList[$sku] = array(
							'item' => $fitmentItems[$sku],
							'product' => $product,
						);
					}
				}
			} catch (Exception $e) {
				Mage::logException($e);
			}
		}
		return $this->_productList;
	}



	public function getPriceInfo()
	{
		$minPrice = 0;
		foreach($this->getProducts() as $item) {
			$minPrice = $minPrice ? min($minPrice, $item['product']['min_price']) : $item['product']['min_price'];
		}

		$itemsCount = count($this->getProducts());

		return array(
			'Price' => $minPrice,
			'HasPriceRange' => ($itemsCount > 1),
			'CanShow' => ($itemsCount > 0)
		);
	}



	public function getProduct()
	{
		$this->getProductInfo($this->getAriProductId());

		if(!$this->_productInfo) {
			return null;
		}

		$elements = $this->getElements();
		$tmsProductIds = $this->getTmsProductIds();
		$products = $this->getProducts();
		$priceInfo = $this->getPriceInfo();

		if(	!$priceInfo['CanShow']
		&&	$tmsProductIds['configurable']
		) {
			$priceInfo = array(
				'Price' => floatval(trim($elements['price'], ' $')),
				'HasPriceRange' => true, // (int)$elements['hasPriceRange'], // this is a configurable product anyway
				'CanShow' => true,
			);
		}

		return array(
			'Id' => $this->_productInfo['Id'],
			'tmsProductIds' => $tmsProductIds,
			'ImageUrl' => $this->_productInfo['ImageUrl'],
			'Brand' => $this->_productInfo['Brand'],
			'Name' => $this->_productInfo['Name'],
			'Description' => $this->_productInfo['Description'],
			'InStock' => ($tmsProductIds['simple'] || $tmsProductIds['configurable']),
			'SimpleProductURL' => ((1 == count($products)) && $tmsProductIds['simple'])
				?	Mage::getUrl() . Mage::helper('fitment')->getRewrittenProductUrl($tmsProductIds['simple']) . ($tmsProductIds['configurable'] ? '?from=fitment' : '')
				:	'',
			'PriceInfo' => $priceInfo,
			'IsOnSale' => (int)$elements['isOnSale'],
			'Fits' => $this->getFitmentId() ? $this->getVehicle() : '',
		);
	}



	public function getViewSkuUrlTemplate()
	{
		$params = array('sku' => '%sku%');

		if(null !== $tmsActivityId = $this->getTmsActivity()) {
			$params['activity'] = $tmsActivityId;
		}

		if($fitmentId = $this->getFitmentId()) {
			$params['fitment'] = $fitmentId;

			if($vehicleName = $this->getVehicle()) {
				$params['vehicle'] = $vehicleName;
			}
		}

		return $this->getUrl('fitment/index/view', $params);
	}

}