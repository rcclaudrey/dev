<?php

class Vikont_Wholesale_ApplicationController extends Mage_Core_Controller_Front_Action
{

	public function indexAction()
	{
		$session = Mage::getSingleton('customer/session');

		if($session->isLoggedIn()) {
			$session->unsBeforeAuthUrl();
		} else {
			$session->setBeforeAuthUrl(Mage::getUrl('*/*/*', array('_current' => true)));
		}

		if(Vikont_Wholesale_Helper_Data::isActiveDealer()) {
			$this->getResponse()->setRedirect(Mage::getUrl('wholesale'));
			return;
		}

		$this
			->loadLayout()
			->_initLayoutMessages('customer/session')
			->_initLayoutMessages('catalog/session')
			->renderLayout();
	}



	public function addressViewAction()
	{
		$addressId = $this->getRequest()->getParam('id');

		$result = array(
			'errorMessage' => '',
		);

		try {
			$address = Mage::getModel('customer/address')->load($addressId);

			$customer = $address->getCustomer();
			if($customer) {
				$address->setEmail($customer->getEmail());
			}

			$result['html'] = $this->getLayout()
					->createBlock('core/template')
						->setTemplate('vk_wholesale/application/address/view.phtml')
						->setAddress($address)
						->toHtml();

		} catch (Exception $e) {
			Mage::logException($e);
			$result['errorMessage'] = $e->getMessage();
		}

		$this->getResponse()->setBody(json_encode($result));
	}



	public function postApplicationAction()
	{
		if (!$this->_validateFormKey()
		||	!$this->getRequest()->isPost()
		) {
            return $this->_redirect('*/*/');
        }

		$data = $this->getRequest()->getPost();

		Mage::helper('wholesale')->checkLogin(Mage::getUrl('*/*/index'), $data);

		$response = array(
			'errorMessage' => '',
			'errorMessages' => array(),
			'messages' =>array(),
		);

		$session = Mage::getSingleton('customer/session');
		$customer = $session->getCustomer();
		$storeId = Mage::app()->getStore()->getId();

		try {
			if('exists' == $data['address_source']) {
				$address = $customer->getAddressById($data['address_id']);
				if(!$address->getId() || $address->getCustomerId() != $customer->getId()) {
					Mage::throwException('Invalid address ID');
				}
			} else {
				$errors = array();
				$address = Mage::getModel('customer/address');
				$addressForm = Mage::getModel('customer/form')
						->setFormCode('customer_address_edit')
						->setEntity($address);
				$addressData = $addressForm->extractData($this->getRequest());
				$addressErrors = $addressForm->validateData($addressData);
				if ($addressErrors !== true) {
					$errors = $addressErrors;
				}

				$addressForm->compactData($addressData);
                $address
					->setCustomerId($customer->getId())
                    ->setIsDefaultBilling(@$data['default_billing'])
                    ->setIsDefaultShipping(@$data['default_shipping']);

				$addressErrors = $address->validate();
                if ($addressErrors !== true) {
                    $errors = array_merge($errors, $addressErrors);
                }

                if (!count($errors)) {
                    $address->save();
					$data['address_id'] = $address->getId();
                    $response['messages'][] = $this->__('The address has been saved');
                } else {
                    $this->_getSession()->setAddressFormData($data);

					$response['errorMessages'] = array();
                    foreach ($errors as $errorMessage) {
                        $response['errorMessages'][] = $errorMessage;
                    }
                }
			}

			// cleaning the data by removing address fields from there
			foreach(array(
				'firstname', 'lastname', 'company', 'telephone', 'fax', 'street', 'city', 'region_id', 'region', 'postcode', 'country_id', 'default_billing', 'default_shipping',
				'form_key', 'i_confirm', 'address_source'
				) as $fieldName) {
				unset($data[$fieldName]);
			}

			$contactEmail = (isset($data['email']) && $data['email'])
				?	$data['email']
				:	$customer->getEmail();

			$application = new Varien_Object($data);
			$application->setData('date_sent', Vikont_Wholesale_Helper_Data::getDateFormatted(time(), 'MMMM d, YYYY'));

			$authKey = md5(rand(10000, 10000000));
			$application->setData('auth_key', $authKey);

//			$customerGroup = Mage::getModel('customer/group')->load($customer->getGroupId());

			$customer
				->setData(Vikont_Wholesale_Helper_Data::ATTR_DEALER_STATUS, Vikont_Wholesale_Model_Source_Dealer_Status::CANDIDATE)
				->setData(Vikont_Wholesale_Helper_Data::ATTR_APPLICATION_DATA, $application)
//				->setData(Vikont_Wholesale_Helper_Data::ATTR_DEALER_COST, $customerGroup->getData('cost_percent'))
				->save();

			// sending a notification to admin
			$adminEmailTemplate = Mage::getModel('core/email_template')
				->setDesignConfig(array(
						'area'  => 'adminhtml',
						'store' => Mage_Core_Model_App::ADMIN_STORE_ID,
					))
//				->setReplyTo($customer->getEmail())
				->sendTransactional(
					Mage::getStoreConfig('wholesale/email/admin_notify_template'),
					Mage::getStoreConfig('wholesale/email/sender_email_identity'),
					Mage::getStoreConfig('wholesale/email/admin_notify_email'),
					$this->__('Dealer Application'),
					array(
						'application' => $application,
						'address' => $address,
						'customer' => $customer,
						'address_html' => $address->format('html'),
						'approve_url' => Mage::getUrl('*/*/status', array(
							'action' => Vikont_Wholesale_Model_Source_Dealer_Status::APPROVED,
							'id' => $customer->getId(),
							'key' => $authKey
						)),
						'decline_url' => Mage::getUrl('*/*/status', array(
							'action' => Vikont_Wholesale_Model_Source_Dealer_Status::DECLINED,
							'id' => $customer->getId(),
							'key' => $authKey
						)),
					),
					$storeId
				);

			if (!$adminEmailTemplate->getSentSuccess()) {
				Mage::log($this->__('Error sending dealer application admin notification email, customer: %s',
					Mage::getStoreConfig('wholesale/email/admin_notify_email')
				));
			}

			// sending a notification to customer
			$dealerEmailTemplate = Mage::getModel('core/email_template')
				->setDesignConfig(array(
						'area'  => 'frontend',
						'store' => $storeId,
					))
				->setReplyTo(Mage::getStoreConfig('wholesale/email/sender_email_identity'))
				->sendTransactional(
					Mage::getStoreConfig('wholesale/email/customer_notify_template'),
					Mage::getStoreConfig('wholesale/email/sender_email_identity'),
					$contactEmail,
					$customer->getName(),
					array(
						'application' => $application,
						'address' => $address,
						'customer' => $customer,
						'address_html' => $address->format('html'),
					),
					$storeId
				);

			$dealerNotificationEmailSent = $dealerEmailTemplate->getSentSuccess();

			if (!$dealerNotificationEmailSent) {
				Mage::log($this->__('Error sending dealer application confirmation email to %s',
					$customer->getName() . ' <'.$contactEmail.'>'
				));
			}

			// responding to AJAX request
			$response['html'] = $this->getLayout()
					->createBlock('core/template')
						->setTemplate('vk_wholesale/application/success.phtml')
						->setApplication($application)
						->setAddress($address)
						->setCustomer($customer)
						->setEmailSent($dealerNotificationEmailSent)
						->toHtml();
		} catch (Exception $e) {
			Mage::logException($e);
			$response['errorMessage'] = $e->getMessage();
		}

		$this->getResponse()->setBody(json_encode($response));
	}



	protected function _changeDealerStatus($status, $statusName)
	{
		$customerId = $this->getRequest()->getParam('id');
		$result = false;

		try {
			if($customerId) {
				$customer = Mage::getModel('customer/customer')->load($customerId);
			}

			if(!$customerId || $customer->getId() !== $customerId) {
				Mage::throwException('No such customer');
			}

			$application = $customer->getData(Vikont_Wholesale_Helper_Data::ATTR_APPLICATION_DATA);
			$hash = $application->getData('auth_key');

			if(!$hash || $hash !== $this->getRequest()->getParam('key')) {
				Mage::throwException('Wrong security key');
			}

			$dealerStatus = $customer->getData(Vikont_Wholesale_Helper_Data::ATTR_DEALER_STATUS);

			if(!in_array($dealerStatus, array(
				Vikont_Wholesale_Model_Source_Dealer_Status::CANDIDATE,
//				Vikont_Wholesale_Model_Source_Dealer_Status::APPROVED,
//				Vikont_Wholesale_Model_Source_Dealer_Status::DECLINED,
//				Vikont_Wholesale_Model_Source_Dealer_Status::TERMINATED
			))) {
				Mage::throwException('Wrong dealer status');
			}

//			$application->unsData('auth_key'); // unsetting the key, thus making current action disposable
//			$customer->setData(Vikont_Wholesale_Helper_Data::ATTR_APPLICATION_DATA, $application)

			$customer
				->setData(Vikont_Wholesale_Helper_Data::ATTR_DEALER_STATUS, $status)
				->save();

			Mage::getSingleton('core/session')->addSuccess($this->__('Dealer application from customer %s <%s> has been %s', $customer->getName(), $customer->getEmail(), $statusName));

			$result = true;

		} catch (Exception $e) {
			Mage::log('Error assigning dealer status to a customer: Message=%s, Customer ID=%s, Status=%s, Remote IP=%s',
					$e->getMessage(),
					$customerId,
					$statusName,
					$_SERVER['REMOTE_ADDR']
				);
			Mage::getSingleton('core/session')->addError('Action error');
		}

		return $result;
	}



	public function statusAction()
	{
		$status = $this->getRequest()->getParam('action');

		switch ($status) {
			case Vikont_Wholesale_Model_Source_Dealer_Status::APPROVED:
				$statusName = 'approved';
				$result = $this->_changeDealerStatus($status, $statusName);
				break;

			case Vikont_Wholesale_Model_Source_Dealer_Status::DECLINED:
				$statusName = 'declined';
				$result = $this->_changeDealerStatus($status, $statusName);
				break;

			default:
				$statusName = 'not set: status unknown';
				$result = false;
		}

		$this
			->loadLayout()
			->_initLayoutMessages('customer/session')
			->_initLayoutMessages('catalog/session');

		$this->getLayout()->getBlock('wholesale_application_status')
			->setResult($result)
			->setDealerStatus($status)
			->setDealerStatusName($statusName)
			->setCustomer(Mage::getModel('customer/customer')->load($this->getRequest()->getParam('id')));

		$this->renderLayout();
	}



	public function testAction()
	{
		$this->_redirect();
	}

}