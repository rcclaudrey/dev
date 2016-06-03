<?php

class Vikont_ARIOEM_Block_Part_Landing_Assembly extends Mage_Core_Block_Template
{
	protected $_params = null;


	protected function _construct()
	{
		$this->setTemplate('arioem/part/landing/assembly.phtml');
		return parent::_construct();
	}



	public function setParams($data)
	{
		$this->_params = new Varien_Object($data);
		return $this;
	}



	public function getParam($paramName)
	{
		return $this->_params->getData($paramName);
	}



	public function getAssemblyList()
	{
		
		return $items;
	}


}