<?php

class Vikont_Wholesale_Model_Observer
{

	public function adminhtml_customer_prepare_save($data)
	{
		$accountData = $data['request']->getParam('account');

		// dealer status
		$dealerStatus = (isset($accountData['dealer_status']) && null !== $accountData['dealer_status'])
				?	$accountData['dealer_status']
				:	Vikont_Wholesale_Model_Source_Dealer_Status::NONE;

		$data['customer']->setData('dealer_status', $dealerStatus);

		// dealer_cost
		$data['customer']->setData('dealer_cost',
				isset($accountData['dealer_cost'])
				?	$accountData['dealer_cost']
				:	null
			);

		// emailing
		$origData = $data['customer']->getOrigData();

		if(	isset($accountData['dealer_status_customer_notify'])
		&&	$accountData['dealer_status_customer_notify']
		&&	isset($origData['dealer_status'])
		&&	($origData['dealer_status'] != $dealerStatus)
		) {
			Mage::register('customer_previous_dealer_status', $origData['dealer_status']);
		}
	}



	public function customer_save_after($data)
	{
		$customer = $data->getData('customer');

		Mage::helper('wholesale/email')->notifyOnCustomerChange($customer);

		// saving this value doesn't make much sense, just indicating we need to inform a customer on his status change
		$previousDealerStatus = Mage::registry('customer_previous_dealer_status');

		if(null !== $previousDealerStatus) {
			$currentDealerStatus = $customer->getData(Vikont_Wholesale_Helper_Data::ATTR_DEALER_STATUS);

			switch($currentDealerStatus) {
//				case Vikont_Wholesale_Model_Source_Dealer_Status::NONE:
//					// do nothing
//					break;
//
//				case Vikont_Wholesale_Model_Source_Dealer_Status::CANDIDATE:
//					// do nothing
//					break;

				case Vikont_Wholesale_Model_Source_Dealer_Status::APPROVED:
					// send the "approved" email
					Mage::helper('wholesale/email')->notifyCustomer($customer->getId(), 'dealer_status_approved');
					break;

				case Vikont_Wholesale_Model_Source_Dealer_Status::DECLINED:
					Mage::helper('wholesale/email')->notifyCustomer($customer->getId(), 'dealer_status_declined');
					// send the "declined" email
					break;

//				case Vikont_Wholesale_Model_Source_Dealer_Status::TERMINATED:
//					// do nothing as we don't have the template for the case (or we do?)
//					break;
			}
		}
	}



	public function core_block_abstract_to_html_before($data)
	{
		if($data['block'] instanceof Mage_Customer_Block_Account_Navigation) {
			$dealerStatus = (int)Mage::getSingleton('customer/session')
					->getCustomer()
						->getData(Vikont_Wholesale_Helper_Data::ATTR_DEALER_STATUS);

			if(in_array($dealerStatus, array(
					Vikont_Wholesale_Model_Source_Dealer_Status::CANDIDATE,
					Vikont_Wholesale_Model_Source_Dealer_Status::APPROVED,
//					Vikont_Wholesale_Model_Source_Dealer_Status::DECLINED,
//					Vikont_Wholesale_Model_Source_Dealer_Status::TERMINATED,
			))) {
				$data['block']->addLink('customer_account_wholesale', 'wholesale/dealer/corner', Mage::helper('wholesale')->__('Dealer Corner'));
			}
		}
	}



	public function payment_method_is_active($observer)
	{
		$quote = $observer->getQuote();
		if ($quote) {
			$customer = $quote->getCustomer();
			$customerGroupId = $customer->getGroupId();
			if ($customerGroupId) { // filtering by registered customers
				$customerGroupsAllowed = explode(',', Mage::getStoreConfig('wholesale/ordering_manual/wholesale_customer_groups'));
				if(in_array($customerGroupId, $customerGroupsAllowed)) {
					$paymentMethodsAllowed = explode(',', Mage::getStoreConfig('wholesale/ordering_manual/payment_methods_allowed'));
					$observer->getEvent()->getResult()->isAvailable = in_array(
							$observer->getMethodInstance()->getCode(),
							$paymentMethodsAllowed
						);
				}
			}
		}
	}

}