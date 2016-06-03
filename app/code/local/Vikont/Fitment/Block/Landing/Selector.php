<?php

class Vikont_Fitment_Block_Landing_Selector extends Vikont_Fitment_Block_Abstract
{

	protected function _construct()
	{
		parent::_construct();
		$this->setTemplate('vk_fitment/landing/selector.phtml');
	}



	public function getCategory()
	{
		if(	($category = $this->getData('category'))
		&&	($category instanceof Mage_Catalog_Model_Category)
		) {
			return $category;
		}
		return new Varien_Object();
	}



	public function getTmsActivityId()
	{
		$tmsActivityId = $this->getCategory()->getTmsActivityId();

		if(null === $tmsActivityId) {
			return parent::getTmsActivityId();
		} else {
			return $tmsActivityId;
		}
	}



	public function getAriCategoryId()
	{
		return $this->getCategory()->getAriCategoryId();
	}



	public function getAriSubcategoryId()
	{
		return $this->getCategory()->getAriSubcategoryId();
	}

}