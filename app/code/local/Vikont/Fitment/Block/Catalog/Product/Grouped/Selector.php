<?php

class Vikont_Fitment_Block_Catalog_Product_Grouped_Selector extends Vikont_Fitment_Block_Catalog_Product_Selector
{
	protected $_allowedProductTypes = array(Mage_Catalog_Model_Product_Type::TYPE_GROUPED);


	protected function _construct()
	{
		parent::_construct();
		$this->setTemplate('vk_fitment/catalog/product/grouped/selector.phtml');
	}



	public function getTmsActivityId()
	{
		$currentCategory = Mage::registry('current_category');

		if($currentCategory instanceof Mage_Catalog_Model_Category) {
			$activityId = Vikont_Fitment_Helper_Data::getActivityIdFromCategory($currentCategory->getId(), 'tms_activity_id');
		}

		if(null === $activityId) {
			$activityId = parent::getTmsActivityId();
		}

		return $activityId;
	}



	public function getCurrentCategoryId()
	{
		$currentCategory = Mage::registry('current_category');

		if($currentCategory instanceof Mage_Catalog_Model_Category) {
			return $currentCategory->getId();
		}

		return null;
	}



	public function getAriProductId()
	{
		$res = $this->getProduct()->getAriProductId(); // same as parent::getAriProductId(); but more clear

		if(!$res) {
			if(Mage_Catalog_Model_Product_Type::TYPE_GROUPED == $this->_product->getTypeId()) {
//				$ariSKU = rtrim($this->_product->getSku(), 'g'); // we can also remove that trailing "g" and load the appropriate simple product by SKU
				$firstChild = $this->_product->getTypeInstance()->getAssociatedProductCollection()
						->addAttributeToSelect('ari_product_id')
						->setPageSize(1)
						->getFirstItem();

				if($firstChild) {
					$res = $firstChild->getAriProductId();
				}
			}
		}

		return $res;
	}



	public function getSelectorConfig()
	{
		$tmsActivityId = $this->getTmsActivityId();
		$ariProductId = $this->getAriProductId();
		$ride = $this->getCurrentRide();

		return array(
			'baseURL' => rtrim($this->getUrl('fitment/index/fitment'), '/'),
			'saveFitmentURL' => $this->getUrl('fitment/grouped/fitmentSave'),
			'activity' => $tmsActivityId,
			'product' => $ariProductId,
			'parentProduct' => $this->getProduct()->getId(),
			'fitment' => array(
				'id' => $ride['id'],
				'name' => $ride['name'],
			),
			'emptyText' => array(
				'makeSelect'	=> $this->__('-- Select make --'),
				'yearSelect' => $this->__('-- Select year --'),
				'modelSelect' => $this->__('-- Select model --'),
				'rideName' => $this->__('Not selected'),
				'noResultMessage' => $this->__('No options available for this configuration'),
			),
			'errorMessage' => $this->__('Fitment currently is not available. Please contact site administrator'),
//			'' => '',
		);
	}

}