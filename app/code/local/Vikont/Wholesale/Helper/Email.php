<?php

class Vikont_Wholesale_Helper_Email extends Mage_Core_Helper_Abstract
{

	protected function prepareCustomerEmailData($customerId)
	{
		$customer = Mage::getModel('customer/customer')->load($customerId);

		$data = array();

		$data['customer'] = $customer;
		$data['application'] = $customer->getData(Vikont_Wholesale_Helper_Data::ATTR_APPLICATION_DATA);
		$data['address'] = Mage::getModel('customer/address');

		if($data['application']) {
			$data['address']->load($data['application']->getData('address_id'));
		} else {
			$data['application'] = new Varien_Object(array(
				'full_name' => $customer->getName(),
				'date_sent' => Vikont_Wholesale_Helper_Data::getDateFormatted(time(), 'MMMM d, YYYY'),
				// what else do email templates use from the application object?
			));

			if($primaryBillingAddress = $customer->getPrimaryBillingAddress()) {
				$data['address'] = $primaryBillingAddress;
			}
		}

		$data['address_html'] = $data['address']->format('html');
		$data['view_profile_url'] = Mage::getUrl('customer/account');

		return $data;
	}



	public function notifyCustomer($customerId, $subject, $data = array())
	{
		$storeId = Mage::app()->getStore()->getId();

		switch($subject) {
			case 'dealer_application_sent':
				$template = Mage::getStoreConfig('wholesale/email/customer_notify_template');
				$data = array_merge($data, $this->prepareCustomerEmailData($customerId));
				$email = $data['application']->getEmail();
				$where = $email ? $email : $data['customer']->getEmail();
				$whom = $data['customer']->getName();
				break;

			case 'dealer_status_approved':
				$template = Mage::getStoreConfig('wholesale/email/customer_confirm_template');
				$data = array_merge($data, $this->prepareCustomerEmailData($customerId));
				$email = $data['application']->getEmail();
				$where = $email ? $email : $data['customer']->getEmail();
				$whom = $data['customer']->getName();
				break;

			case 'dealer_status_declined':
				$template = Mage::getStoreConfig('wholesale/email/customer_decline_template');
				$data = array_merge($data, $this->prepareCustomerEmailData($customerId));
				$email = $data['application']->getEmail();
				$where = $email ? $email : $data['customer']->getEmail();
				$whom = $data['customer']->getName();
				break;

			default:
				return true;
		}

		$emailTemplate = Mage::getModel('core/email_template')
			->setDesignConfig(array(
					'area'  => 'frontend',
					'store' => $storeId,
				))
			->setReplyTo(Mage::getStoreConfig('wholesale/email/sender_email_identity'))
			->sendTransactional(
				$template,
				Mage::getStoreConfig('wholesale/email/sender_email_identity'),
				$where,
				$whom,
				$data,
				$storeId
			);

		return $emailTemplate->getSentSuccess();
	}



	public function notifyAdmin()
	{

	}

}