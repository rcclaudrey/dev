<?php

class Vikont_ARIOEM_Block_Part_Selector extends Mage_Core_Block_Template
{

	protected function _construct()
	{
		$this->setTemplate('arioem/part/selector.phtml');

		// here we need to let Magento independent ARI API gateway know what's the current customer cost
		Mage::getSingleton('customer/session')
			->unsCustomerCostPercent()
			->setCustomerCostPercent(Mage::helper('arioem')->getCustomerCostPercent());

		return parent::_construct();
	}



	public function getBrands()
	{
		$helper = Mage::helper('arioem');
		$brands = $helper->getBrands();

		$currentBrand = $helper->getCurrentBrandName();
		if ($currentBrand) {
			$currentBrandCode = Vikont_ARIOEM_Helper_Data::brandName2Code($currentBrand);
			if ($currentBrandCode && isset($brands[$currentBrandCode])) {
				return array($currentBrandCode => $brands[$currentBrandCode]);
			}
		}

		return $brands;
	}

}