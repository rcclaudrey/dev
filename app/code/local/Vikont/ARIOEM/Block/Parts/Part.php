<?php

class Vikont_ARIOEM_Block_Parts_Part extends Mage_Core_Block_Template
{
	protected $_partInfoModel = null;


	protected function _construct()
	{
		$this->setTemplate('arioem/parts/part.phtml');
		$this->_partInfoModel = Mage::getSingleton('arioem/oem_part');

		return parent::_construct();
	}



	public function getPart()
	{
		return $this->_partInfoModel;
	}

}