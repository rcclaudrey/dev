<?php

class Vikont_Wholesale_Block_Application_Address_Edit extends Mage_Customer_Block_Address_Edit
{

	protected function _construct()
	{
		parent::_construct();

		$this->setTemplate('vk_wholesale/application/address/edit.phtml');

		$this->_address = Mage::getModel('customer/address');
		$this->_address
			->setPrefix($this->getCustomer()->getPrefix())
			->setFirstname($this->getCustomer()->getFirstname())
			->setMiddlename($this->getCustomer()->getMiddlename())
			->setLastname($this->getCustomer()->getLastname())
			->setSuffix($this->getCustomer()->getSuffix())
			->setEmail($this->getCustomer()->getEmail());
	}



	public function getAddress()
	{
		return parent::getAddress();
	}


	protected function _prepareLayout()
	{
		// do nothing; just overriding the excessive stuff
	}

}

