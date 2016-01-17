<?php

class Vikont_Fitment_Block_Fitment_Toolbar extends Vikont_Fitment_Block_Fitment_Abstract
{
	const VIEW_MODE_GRID = 'grid';
	const VIEW_MODE_LIST = 'list';


	protected static $_sortingOptions = array(
			'Relevancy' => array(
				'label' => 'Search',
				'title' => 'Sort by relevancy based on search term',
			),
			'Rating' => array(
				'label' => 'Rating',
				'title' => 'Sort by highest rating',
			),
			'priceASC' => array(
				'label' => 'Price Asc',
				'title' => 'Sort by price ascending',
			),
			'priceDESC' => array(
				'label' => 'Price Desc',
				'title' => 'Sort by price descending',
			),
			'nameASC' => array(
				'label' => 'Name Asc',
				'title' => 'Sort by name ascending',
			),
			'nameDESC' => array(
				'label' => 'Name Desc',
				'title' => 'Sort by name descending',
			),
		);

	protected static $_defaultSortCriteria = 'Rating';


	protected function _construct()
	{
		parent::_construct();

		$this->setTemplate('vk_fitment/fitment/toolbar.phtml');
	}



	public static function getSortingOptions()
	{
		return self::$_sortingOptions;
	}



	public static function getDefaultSort()
	{
		return self::$_defaultSortCriteria;
	}



	public function getCurrentSort()
	{
		return isset(self::$_ariParams['options']['sort']) ? self::$_ariParams['options']['sort'] : self::getDefaultSort();
	}



	public static function getViewModes()
	{
		return Vikont_Fitment_Block_Fitment_List::getViewModes();
	}



	public static function getViewMode()
	{
		return Vikont_Fitment_Block_Fitment_List::getViewMode();
	}

}