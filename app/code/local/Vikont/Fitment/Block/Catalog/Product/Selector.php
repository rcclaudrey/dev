<?php

class Vikont_Fitment_Block_Catalog_Product_Selector extends Vikont_Fitment_Block_Abstract
{
	protected $_product = null;
	protected $_params = null;
	protected $_allowedProductTypes = array(
		Mage_Catalog_Model_Product_Type::TYPE_SIMPLE,
		Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE,
	);


	protected function _construct()
	{
		parent::_construct();
		$this->_params = Mage::app()->getRequest()->getParams();
		$this->setTemplate('vk_fitment/catalog/product/selector.phtml');
	}



	protected function _toHtml()
	{
		if(!in_array($this->getProduct()->getTypeId(), $this->_allowedProductTypes)) {
			return '';
		}

		return $this->getProductHasFitments()
			?	parent::_toHtml()
			:	'';
	}



	public function getTmsActivityId()
	{
		if(null === self::$_activityId) {
			self::$_activityId = Mage::app()->getRequest()->getParam('activity');

			if(null === self::$_activityId) {
				$currentProduct = Mage::registry('current_product');
				if($currentProduct) {
					self::$_activityId = (int) Vikont_Fitment_Helper_Data::getActivityIdFromProduct($currentProduct);
				}
			}

			if(null === self::$_activityId) {
				self::$_activityId = parent::getTmsActivityId();
			}
		}

		return self::$_activityId;
	}



	public function getAriProductId()
	{
		return $this->_product->getAriProductId();
	}



	public function getProductHasFitments()
	{
		$this->getProduct();

		if($this->_product) {
			$hasFitment = $this->_product->getAriHasFitment();

			if(null === $hasFitment) {
				$ariProductId = $this->getAriProductId();

				if($ariProductId) {
					$productInfo = Mage::helper('fitment/api')
						->preventErrorReporting()
						->request('product', array('productId' => $ariProductId));

					if($productInfo) {
						$hasFitment = $productInfo['HasFitments'];
					}
				}
			}
			return $hasFitment;
		}
		return false;
	}



	public function setProduct($product)
	{
		$this->_product =$product;

		return $this;
	}



	public function getProduct()
	{
		if(null === $this->_product) {
			$product = Mage::registry('current_product');

			if(!$product) {
				$product = false;
			}
			$this->_product = $product;
		}
		return $this->_product;
	}



	public function isRideCompatibleToProduct()
	{
		$this->getProduct();
		if($this->_product) {
			$ariSkuId = $this->_product->getAriProductSku(); // $this->_product->getSku();
			$ariProductId = $this->_product->getAriProductId();
			$params = array(
				'productId' => $ariProductId,
			);
			$ride = Mage::helper('fitment')->getCurrentRide($this->getTmsActivityId());
			if(isset($ride['id']) && $ride['id']) {
				$params['fitmentId'] = $ride['id'];
			}

			$fitmentNotes = Mage::helper('fitment/api')
					->preventErrorReporting()
					->request('fitmentnotes',  $params);

			if(!$fitmentNotes) return false;

			foreach($fitmentNotes as $note) {
				if($note['SkuId'] == $ariSkuId) {
					return true;
				}
			}
		}
		return false;
	}



	public function getCurrentRide()
	{
		$tmsActivityId = $this->getTmsActivityId();

		if(isset($this->_params['fitment'])) {
			$vehicleName = isset($this->_params['vehicle']) ? $this->_params['vehicle'] : '';
			$ride = Mage::helper('fitment')->completeRideInfo($tmsActivityId, $this->_params['fitment'], $vehicleName);
		} else {
//			$ride = Mage::helper('fitment')->getCurrentRide($tmsActivityId);
			$ride = Mage::helper('fitment')->getDefaultRide($tmsActivityId); // this cancels using pre-selected vehicle
		}

		return $ride;
	}



	public function getFits()
	{
		return (bool) (isset($this->_params['fitment']) ? $this->_params['fitment'] : '');
	}

}