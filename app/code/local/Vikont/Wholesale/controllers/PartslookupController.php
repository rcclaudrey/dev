<?php

class Vikont_Wholesale_PartslookupController extends Mage_Core_Controller_Front_Action
{

	public function indexAction()
	{
		$session = Mage::getSingleton('customer/session');

		if(!$session->authenticate($this, Mage::getUrl('*/*/login'))) return;

		if(!Vikont_Wholesale_Helper_Data::isActiveDealer()) {
			$this->getResponse()->setRedirect(Mage::getUrl('wholesale/dealer/corner'));
			return;
		}

		$this->loadLayout()->renderLayout();
	}

}