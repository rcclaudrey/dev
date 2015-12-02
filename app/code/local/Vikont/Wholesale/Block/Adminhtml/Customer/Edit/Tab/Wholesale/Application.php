<?php

class Vikont_Wholesale_Block_Adminhtml_Customer_Edit_Tab_Wholesale_Application extends Mage_Adminhtml_Block_Template
{

    public function _construct()
    {
		parent::_construct();
		$this->setTemplate('vk_wholesale/customer/edit/tab/wholesale/application.phtml');
    }



	public function getCustomer()
	{
		return Mage::registry('current_customer');
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
		if($this->hasApplication()) {
			$contactEmail = $this->getApplicationData()->getEmail();

			$address = Mage::getModel('customer/address')
					->load($this->getApplicationData()->getAddressId());

			$address->setEmail($contactEmail
					?	$contactEmail
					:	$this->getCustomer()->getEmail()
				);

			return $address;
		}
		return new Varien_Object();
	}

}