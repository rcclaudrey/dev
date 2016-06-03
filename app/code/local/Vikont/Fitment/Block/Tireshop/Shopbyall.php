<?php

class Vikont_Fitment_Block_Tireshop_Shopbyall extends Vikont_Fitment_Block_Abstract
{

	protected function _construct()
	{
		parent::_construct();
		$this->setTemplate('vk_fitment/tireshop/shopbyall.phtml');
	}



	public function getTiresCategoryId()
	{
		$ariActivityId = Vikont_Fitment_Helper_Data::getTmsActivity($this->getTmsActivityId(), 'ari_activity');
		return Mage::helper('fitment')->getTiresCategoryId($ariActivityId);
	}

}