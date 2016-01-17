<?php

class Vikont_Wholesale_Block_Dealer_Corner extends Mage_Core_Block_Template
{

	protected function _construct()
	{
		parent::_construct();
		$this->setTemplate('vk_wholesale/dealer/corner.phtml');
	}



	public function getCustomer()
	{
		return Mage::getSingleton('customer/session')->getCustomer();
	}



	public function getDealerStatus()
	{
		$dealerStatus = $this->getCustomer()->getData(Vikont_Wholesale_Helper_Data::ATTR_DEALER_STATUS);
		if(null === $dealerStatus) {
			$dealerStatus = Vikont_Wholesale_Model_Source_Dealer_Status::NONE;
		}
		return $dealerStatus;
	}



	public function hasApplication()
	{
		return (boolean) $this->getCustomer()->getData(Vikont_Wholesale_Helper_Data::ATTR_APPLICATION_DATA);
	}



	public function getApplicationData()
	{
		$data = $this->getCustomer()->getData(Vikont_Wholesale_Helper_Data::ATTR_APPLICATION_DATA);
		return $data ? $data : new Varien_Object();
	}



	public function getAddress()
	{
		if($this->getApplicationData()) {
			return Mage::getModel('customer/address')->load($this->getApplicationData()->getAddressId());
		}
		return new Varien_Object();
	}

}

