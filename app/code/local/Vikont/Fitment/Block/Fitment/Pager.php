<?php

class Vikont_Fitment_Block_Fitment_Pager extends Vikont_Fitment_Block_Fitment_Abstract
{
	protected static $_pageSizesAvailable = array(12, 24, 36, 48, 72, 99);



	protected function _construct()
	{
		parent::_construct();

		$this->setTemplate('vk_fitment/fitment/pager.phtml');
	}



	protected function _toHtml()
	{
		if(static::rideIsRequired()) return '';
		else return parent::_toHtml();
	}



	public static function getPageSizesAvailable()
	{
		return self::$_pageSizesAvailable;
	}



	public static function getDefaultPageSize()
	{
		return self::$_pageSizesAvailable[0];
	}



	public function getFoundTotal()
	{
		return isset(self::$_ariData['NumFound']) ? self::$_ariData['NumFound'] : 0;
	}



	public function getPageSize()
	{
		return isset(self::$_ariParams['options']['take']) ? self::$_ariParams['options']['take'] : self::getDefaultPageSize();
	}



	public function getCurrentPage()
	{
		return isset(self::$_ariParams['options']['skip']) ? floor(self::$_ariParams['options']['skip'] / self::getPageSize()) : 0;
	}



	public function getPagesTotal()
	{
		return ceil(self::getFoundTotal() / self::getPageSize());
	}

}