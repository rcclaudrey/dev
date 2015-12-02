<?php

class Vikont_Wholesale_DealerController extends Mage_Core_Controller_Front_Action
{

	public function cornerAction()
	{
		$session = Mage::getSingleton('customer/session');

		if(!$session->authenticate($this, Mage::getUrl('*/*/login'))) return;

		if(Vikont_Wholesale_Helper_Data::isActiveDealer()) {
			$this->getResponse()->setRedirect(Mage::getUrl('wholesale'));
			return;
		}

		if(!Vikont_Wholesale_Helper_Data::isApplicationSent()) {
			$this->getResponse()->setRedirect(Mage::getUrl('wholesale/application'));
			return;
		}

		$this
			->loadLayout()
			->_initLayoutMessages('customer/session')
			->_initLayoutMessages('catalog/session')
			->renderLayout();
	}



	public function loginAction()
	{
		$session = Mage::getSingleton('customer/session');

		if(!$session->isLoggedIn()) {
			if(!$session->getBeforeAuthUrl()) {
				$session->setBeforeAuthUrl(Mage::getUrl('*/*/corner'));
			}

			$this
				->loadLayout()
				->_initLayoutMessages('customer/session')
				->_initLayoutMessages('catalog/session')
				->renderLayout();
			return;
		}

		if(Vikont_Wholesale_Helper_Data::isActiveDealer()) {
			$this->getResponse()->setRedirect(Mage::getUrl('wholesale'));
			return;
		}

		if(!Vikont_Wholesale_Helper_Data::isApplicationSent()) {
			$this->getResponse()->setRedirect(Mage::getUrl('wholesale/application'));
			return;
		}
	}

}