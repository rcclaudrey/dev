<?php

class Vikont_Wholesale_QuickorderController extends Mage_Core_Controller_Front_Action
{

	protected function _authenticate()
	{
		$session = Mage::getSingleton('customer/session');

		if(!$session->authenticate($this, Mage::getUrl('*/*/login'))) return false;

		if(!Vikont_Wholesale_Helper_Data::isActiveDealer()) {
			$this->getResponse()->setRedirect(Mage::getUrl('wholesale/dealer/corner'));
			return false;
		}

		return true;
	}



	public function indexAction()
	{
		if(!$this->_authenticate()) return;

		$this->loadLayout()->renderLayout();
	}



	public function checkPartNumberAction()
	{
		$result = array(
			'errrorMessage' => '',
			'id' => $this->getRequest()->getParam('id'),
		);

		if(Mage::helper('wholesale')->isLoginRequired()) {
			Mage::getSingleton('customer/session')->setBeforeAuthUrl(
					isset($_SERVER['HTTP_REFERER'])
						?	$_SERVER['HTTP_REFERER']
						:	Mage::getUrl('*/*/index')
				);

			$result['errorMessage'] = 'We\'re sorry, but your browser session has expired. Please relogin.';
			$result['html'] = $this->getLayout()
					->createBlock('core/template')
						->setTemplate('vk_wholesale/quickorder/item/relogin.phtml')
						->toHtml();
		} else {
			try {
				$result['html'] = $this->getLayout()
						->createBlock('wholesale/quickorder_item_checked')
							->setData(Vikont_Wholesale_Block_Quickorder_Item_Checked::PARTNUMBER_DATA_NAME,
									$this->getRequest()->getParam('partNumber'))
							->toHtml();
			} catch (Exception $e) {
				Mage::logException($e);
				$result['errrorMessage'] = $e->getMessage();
			}
		}

		$this->getResponse()->setBody(json_encode($result));
	}



	public function fileUploadAction()
	{
		$fileContents = isset($_FILES['csv'])
			?	Mage::helper('wholesale')->readCsv($_FILES['csv']['tmp_name'])
			:	null;

		Mage::helper('wholesale')->checkLogin(Mage::getUrl('*/*/index'), $fileContents);

		$result = array(
			'html' => '',
			'errorMessage' => '',
		);

		if(Mage::helper('wholesale')->isLoginRequired()) {
			Mage::getSingleton('customer/session')->setBeforeAuthUrl(
					isset($_SERVER['HTTP_REFERER'])
						?	$_SERVER['HTTP_REFERER']
						:	Mage::getUrl('*/*/index')
				);

			$result['errorMessage'] = 'We\'re sorry, but your browser session has expired. Please relogin.';
			$result['redirect'] = Mage::getUrl('customer/account/login');
//			$result['html'] = $this->getLayout()
//					->createBlock('core/template')
//						->setTemplate('vk_wholesale/quickorder/item/relogin.phtml')
//						->toHtml();
		} else {
			try {
				$result['html'] = $this->getLayout()
						->createBlock('wholesale/quickorder_item_uploaded')
							->setData(Vikont_Wholesale_Block_Quickorder_Item_Uploaded::ROWS_DATA_NAME, $fileContents)
							->toHtml();
			} catch (Exception $e) {
				Mage::logException($e);
				$result['errorMessage'] = $e->getMessage();
			}
		}

		$this->getResponse()->setBody(json_encode($result));
	}



	public function orderPostAction()
	{
		$data = $this->getRequest()->getPost();

		$result = array(
			'errorMessage' => '',
		);

		if(!$this->_authenticate()) {
			Mage::getSingleton('customer/session')->setFormData($data);
			$result['errorMessage'] = $this->__('Your browser session has expired. Please relogin and try again.');
			$this->getResponse()->setBody(json_encode($result));
			return;
		}

		if(Mage::helper('wholesale')->isLoginRequired()) {
			Mage::getSingleton('customer/session')->setBeforeAuthUrl(
					isset($_SERVER['HTTP_REFERER'])
						?	$_SERVER['HTTP_REFERER']
						:	Mage::getUrl('*/*/index')
				);

			$result['errorMessage'] = 'We\'re sorry, but your browser session has expired. Please relogin.';
/*
			$result['redirect'] = Mage::getUrl('customer/account/login');
			$result['html'] = $this->getLayout()
					->createBlock('core/template')
						->setTemplate('vk_wholesale/quickorder/item/relogin.phtml')
						->toHtml();
 /**/
		} else {
			try {
				if(strlen($data['poNumber']) > 10) {
					Mage::throwException($this->__('PO Number must not exceed 10 characters'));
				}

				$data['notes'] = 'SHIP ' . strtoupper($data['shippingType']) . ' | ' . $data['notes'];

				$order = Mage::helper('wholesale/order')->createOrder($data);

				$customerSession = Mage::getSingleton('customer/session');
				$customerSession->unsFormData();

				$result['html'] = $this->getLayout()
						->createBlock('core/template')
							->setTemplate('vk_wholesale/quickorder/success.phtml')
							->setOrder($order)
							->toHtml();

			} catch (Exception $e) {
				Mage::logException($e);

				$message = $e->getMessage();

				// 'Please specify a shipping method.'
				if (stristr($message, 'specify') && stristr($message, 'shipping method')) {
					$message = $this->__('Cannot get shipping rates. Please check your default shipping address.');
				}

				$result['errorMessage'] = $message;
			}
		}

		$this->getResponse()->setBody(json_encode($result));
	}

}