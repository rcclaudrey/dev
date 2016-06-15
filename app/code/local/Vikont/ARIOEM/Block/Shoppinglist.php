<?php

class Vikont_ARIOEM_Block_Shoppinglist extends Mage_Core_Block_Template
{

	protected function _construct()
	{
		$this->setTemplate('arioem/shoppinglist.phtml');
		return parent::_construct();		
	}



	public function isAjax()
	{
		return $this->getIsAjax() || Mage::registry('isAJAX');
	}



	public function getItems()
	{
		return Mage::helper('arioem/OEM')->getCartOEMItems();
	}

}