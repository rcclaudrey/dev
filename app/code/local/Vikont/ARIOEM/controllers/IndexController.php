<?php

class Vikont_ARIOEM_IndexController extends Mage_Core_Controller_Front_Action
{

	public function assemblyAction()
	{
		$responseAjax = new Varien_Object(Mage::helper('arioem')->getAssemblyData());
		$this->getResponse()->setBody($responseAjax->toJson());
	}



	public function clearCacheAction()
	{
		$cache = Mage::app()->getCache();
		$cache->clean(array(Vikont_ARIOEM_Helper_Api::MAGE_CACHE_TAG));
	}

}