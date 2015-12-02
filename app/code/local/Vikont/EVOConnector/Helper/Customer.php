<?php

class Vikont_EVOConnector_Helper_Customer extends Mage_Core_Helper_Abstract
{
	const CUSTOMER_TYPE_REGULAR = 'REGULAR';
	const CUSTOMER_TYPE_WHOLESALE = 'WHOLESALE';


	protected static $_customerCache = array();
	protected static $_lastCustomerId = null;


	protected static function _getLastCustomerId($customerId)
	{
		if($customerId) {
			self::$_lastCustomerId = $customerId;
		}

		return self::$_lastCustomerId;
	}



	public function getCustomer($customerId)
	{
		$customerId = self::_getLastCustomerId($customerId);

		if(!isset(self::$_customerCache[$customerId])) {
			self::$_customerCache[$customerId] = Mage::getModel('customer/customer')->load($customerId);
		}

		return self::$_customerCache[$customerId];
	}



	public function getCustomerType($customerId = null)
	{
		$customerId = self::_getLastCustomerId($customerId);

		if(Mage::helper('core')->isModuleEnabled('Vikont_Wholesale')) {
			return ((int)$this->getCustomer($customerId)->getData(Vikont_Wholesale_Helper_Data::ATTR_DEALER_STATUS)
					== Vikont_Wholesale_Model_Source_Dealer_Status::APPROVED)
				?	self::CUSTOMER_TYPE_WHOLESALE
				:	self::CUSTOMER_TYPE_REGULAR;
		} else {
			return self::CUSTOMER_TYPE_REGULAR;
		}
	}



	public function isCustomerWholesale($customerId = null)
	{
		return (self::CUSTOMER_TYPE_WHOLESALE == $this->getCustomerType($customerId));
	}

}