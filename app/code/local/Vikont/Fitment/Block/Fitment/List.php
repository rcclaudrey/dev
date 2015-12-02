<?php

class Vikont_Fitment_Block_Fitment_List extends Vikont_Fitment_Block_Fitment_Abstract
{
	const VIEW_MODE_GRID = 'grid';
	const VIEW_MODE_LIST = 'list';


	protected function _construct()
	{
		parent::_construct();

		$this->setTemplate('vk_fitment/fitment/list.phtml');
	}



	public function getProducts()
	{
		return isset(self::$_ariData['Products']) ? self::$_ariData['Products'] : array();
	}



	public static function getViewModes()
	{
		return array(
			self::VIEW_MODE_GRID => 'Grid',
			self::VIEW_MODE_LIST => 'List',
		);
	}



	public static function getDefaultViewMode()
	{
		return self::VIEW_MODE_GRID;
	}



	public static function getViewMode()
	{
		return isset(self::$_ariParams['viewMode']) ? self::$_ariParams['viewMode'] : self::getDefaultViewMode();
	}



	public static function getFitmentId()
	{
		return isset(self::$_ariParams['options']['fitmentId']) ? self::$_ariParams['options']['fitmentId'] : null;
	}

}