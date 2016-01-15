<?php
/**
 * Observer to limit access to customer groups by customer group
 */
class N98_CustomerGroupCheckout_Model_Payment_Observer
{
	const XML_CUSTOMER_GROUP_CONFIG_FIELD = 'available_for_customer_groups';

	/**
	 * Check if customer group can use the payment method
	 *
	 * @param Varien_Event_Observer $observer
	 * @return bool
	 */
	public function methodIsAvailable(Varien_Event_Observer $observer)
	{
		/* @var $paymentMethodInstance Mage_Payment_Model_Method_Abstract */
		$paymentMethodInstance = $observer->getMethodInstance();

		/* @var $customer Mage_Customer_Model_Customer */
		if(Mage::app()->getStore()->isAdmin()) {
			$customer =  Mage::getSingleton('adminhtml/session_quote')->getCustomer();
		} else {
			$customer =  Mage::getSingleton('customer/session')->getCustomer();
		}

		if ($paymentMethodInstance instanceof Mage_Paypal_Model_Standard) {
			$customerGroupConfig = Mage::getStoreConfig('paypal/wps/' . self::XML_CUSTOMER_GROUP_CONFIG_FIELD);
		} elseif ($paymentMethodInstance instanceof Mage_Paypal_Model_Express) {
			$customerGroupConfig = Mage::getStoreConfig('paypal/express/' . self::XML_CUSTOMER_GROUP_CONFIG_FIELD);
		} elseif ($paymentMethodInstance instanceof Mage_GoogleCheckout_Model_Payment) { // VK: this won't work
			$customerGroupConfig = Mage::getStoreConfig('google/checkout/' . self::XML_CUSTOMER_GROUP_CONFIG_FIELD);
		} else {
			$customerGroupConfig = $paymentMethodInstance->getConfigData(self::XML_CUSTOMER_GROUP_CONFIG_FIELD);
		}

		if ($customerGroupConfig) {
			$customerGroupsAllowed = explode(',', $customerGroupConfig);
			$observer->getResult()->isAvailable = in_array($customer->getGroupId(), $customerGroupsAllowed);
		}
	}

}
