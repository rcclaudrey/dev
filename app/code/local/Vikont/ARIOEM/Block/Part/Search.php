<?php

class Vikont_ARIOEM_Block_Part_Search extends Mage_Core_Block_Template
{

	protected function _construct()
	{
		$this->setTemplate('arioem/part/search.phtml');

		// here we need to let Magento independent ARI API gateway know what's the current customer cost
		Mage::getSingleton('customer/session')
			->unsCustomerCostPercent()
			->setCustomerCostPercent(Mage::helper('arioem')->getCustomerCostPercent());

		return parent::_construct();
	}




}