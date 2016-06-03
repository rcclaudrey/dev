<?php

class Vikont_Fitment_Block_Fitment_Search extends Vikont_Fitment_Block_Fitment_Abstract
{

	protected function _construct()
	{
		parent::_construct();

		$this->setTemplate('vk_fitment/fitment/search.phtml');
	}



	protected function _toHtml()
	{
		if(static::rideIsRequired()) return '';
		else return parent::_toHtml();
	}



	public function getSearchTerm()
	{
		return isset(self::$_ariData['term']) ? self::$_ariData['term'] : '';
	}

}