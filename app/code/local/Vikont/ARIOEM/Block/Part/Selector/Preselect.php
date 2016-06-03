<?php

class Vikont_ARIOEM_Block_Part_Selector_Preselect extends Mage_Core_Block_Template
{
	protected $_hash = null;
	protected $_params = array();


	protected function _construct()
	{
		$this->setTemplate('arioem/part/selector/preselect.phtml');
		return parent::_construct();
	}



	protected function _toHtml()
	{
		if (!$this->getHash()) return '';
		else return parent::_toHtml();
	}



	public function setHash($hash)
	{
		$this->_hash = trim($hash);
		parse_str($this->_hash, $this->_params);
		return $this;
	}



	public function getHash()
	{
		return http_build_query($this->_params);
	}

}