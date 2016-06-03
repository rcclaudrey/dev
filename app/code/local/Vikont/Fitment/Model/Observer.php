<?php

class Vikont_Fitment_Model_Observer
{

	public function application_clean_cache($observer)
	{
		$tags = $observer->getTags();

		if (in_array(Vikont_Fitment_Helper_Api::MAGE_CACHE_TAG, $tags)) {
			Mage::app()->cleanCache(array(Vikont_Fitment_Helper_Api::MAGE_CACHE_TAG));
		}
	}



//	public function controller_action_predispatch_adminhtml_cache_massRefresh($observerData)
//	{
//
//	}



//	public function controller_action_predispatch_adminhtml_cache_flushAll($observerData)
//	{
//
//	}



//	public function controller_action_predispatch_adminhtml_cache_flushSystem($observerData)
//	{
//
//	}



	public function adminhtml_cache_refresh_type($observer)
	{
		if ($observer->getType() == Vikont_Fitment_Helper_Api::MAGE_CACHE_TAG) {
			Mage::app()->cleanCache(array(Vikont_Fitment_Helper_Api::MAGE_CACHE_TAG));
		}
	}



	public function adminhtml_cache_flush_all($observer)
	{
		$types = Mage::app()->getRequest()->getParam('types');

		if (in_array(Vikont_Fitment_Helper_Api::MAGE_CACHE_GROUP, $types)) {
//			Mage::app()->getCache()->flush();
//		Mage::app()->cleanCache(array(Vikont_Fitment_Helper_Api::MAGE_CACHE_TAG));
		}
	}

}