<?php

class Vikont_Wholesale_Helper_Data extends Mage_Core_Helper_Abstract
{
	const ATTR_APPLICATION_DATA = 'application';
	const ATTR_DEALER_STATUS = 'dealer_status';
	const ATTR_DEALER_COST = 'dealer_cost';

	protected static $isEnabled = null;
	protected static $_customerGroupCostPercent = null;
	protected static $_customerCostPercent = null;
	protected static $_customer = null;



	public static function isEnabled()
	{
		if(null === self::$isEnabled) {
			self::$isEnabled = (bool) Mage::getStoreConfig('wholesale/general/enabled');
		}

		return self::$isEnabled;
	}



	public static function getCustomerSession()
	{
		if(Mage::app()->getStore()->isAdmin()) {
			return Mage::getSingleton('adminhtml/session_quote');
		} else {
			return Mage::getSingleton('customer/session');
		}
	}



	public static function getCustomer()
	{
		if(null === self::$_customer) {
			if(Mage::app()->getStore()->isAdmin()) {
				self::$_customer =  Mage::getSingleton('adminhtml/session_quote')->getCustomer();
			} else {
				self::$_customer =  Mage::getSingleton('customer/session')->getCustomer();
			}
		}
		return self::$_customer;
	}



	public static function getCustomerGroupCostPercent()
	{
		if(null === self::$_customerGroupCostPercent) {
			$groupId = self::getCustomer()->getGroupId();

			self::$_customerGroupCostPercent = (Mage_Customer_Model_Group::NOT_LOGGED_IN_ID == $groupId)
				?	0
				:	floatval(Mage::getModel('customer/group')->load($groupId)->getCostPercent());
		}
		return self::$_customerGroupCostPercent;
	}



	public static function getCustomerDealerCostPercent()
	{
		if(null === self::$_customerCostPercent) {
			self::$_customerCostPercent = floatval(self::getCustomer()->getData(self::ATTR_DEALER_COST));

			if(!self::$_customerCostPercent) {
				self::$_customerCostPercent = self::getCustomerGroupCostPercent();
			}
		}
		return self::$_customerCostPercent;
	}



	public function checkLogin($url = '', $data = null)
	{
		$session = Mage::getSingleton('customer/session');

		if(!$session->isLoggedIn()) {
			$session->setBeforeAuthUrl($url ? $url : Mage::getUrl('*/*/*', array('_current' => true)));

			if(null !== $data) {
				if($session->getFormData()) {
					$session->unsFormData();
				}
				$session->setFormData($data);
			}

			Mage::app()->getResponse()->setRedirect(Mage::getUrl('wholesale/dealer/login'));

			return false;
		}
		return true;
	}



	public static function isLoginRequired()
	{
		$session = Mage::getSingleton('customer/session');
		return !$session->isLoggedIn();
	}



	public static function isApplicationSent()
	{
		$dealerStatus = Mage::getSingleton('customer/session')->getCustomer()->getData(self::ATTR_DEALER_STATUS);
		return (null !== $dealerStatus) && (Vikont_Wholesale_Model_Source_Dealer_Status::NONE != (int)$dealerStatus);
	}



	public static function isActiveDealer()
	{
		return (	Vikont_Wholesale_Model_Source_Dealer_Status::APPROVED
				=== (int)Mage::getSingleton('customer/session')->getCustomer()->getData(self::ATTR_DEALER_STATUS));
	}



	public function requireLogin($url = '')
	{
		$session = Mage::getSingleton('customer/session');

		if(!$session->isLoggedIn()) {
			$session->setBeforeAuthUrl($url ? $url : Mage::getUrl('*/*/*', array('_current' => true)));
			Mage::app()->getResponse()->setRedirect(Mage::getUrl('wholesale/dealer/login'));
			return false;
		}
		return true;
	}



	public function readCsv($fileName)
	{
		$result = array();

		if (false !== $handle = fopen($fileName, 'r')) {
			while(false !== $data = fgetcsv($handle)) {
				if(!count($data) || !$data[0]) continue;
				$result[] = $data;
			}
			fclose($handle);
		}

		return $result;
	}



	public function getQuickOrderFields()
	{
		$customer = Mage::getSingleton('customer/session')->getCustomer();
		$address = $customer->getPrimaryBillingAddress();

		return array(
			'company' => $address ? $address->getCompany() : '',
			'contact' => $address ? $address->getName() : '',
			'phone' => $address ? $address->getTelephone() : '',
		);
	}



	public function getDealerApplicationFields()
	{
		return array(
			'address' => '',
			'business_experience_term' => '',
			'amount_spent_for_parts' => '',
			'brands_serviced' => '',
			'switch_reason' => '',
			'seller_permit_no' => '',
			'parts_sold' => '',
			'parts_to_purchase' => '',
			'work_title' => '',
//			'' => '',
		);
	}



	public function getDealerApplicationAddressFields()
	{
		return array(
			'prefix' => '',
			'firstname' => '',
			'middlename' => '',
			'lastname' => '',
			'suffix' => '',
			'company' => '',
			'street' => '',
			'city' => '',
			'country_id' => '',
			'region_id' => '',
			'postcode' => '',
			'telephone' => '',
			'fax' => '',
			'vat_id' => '',
//			'' => '',
		);
	}


	public function calculateOEMPrice($cost, $price = 0, $msrp = 0)
	{
		$gainPercent = self::getCustomerDealerCostPercent();

		if ((float)$gainPercent) {
			return $cost * (100 + $gainPercent) / 100;
		} else {
			$price = floatval($price);
			return $price ? $price : $msrp;
		}
	}



	protected $_wholesalePriceDiscountDisplayRules = null;

	protected function _getWholesalePriceDiscountDisplayRules()
	{
		if (null === $this->_wholesalePriceDiscountDisplayRules) {
			$this->_wholesalePriceDiscountDisplayRules = array();

			$data = explode(';', Mage::getStoreConfig('wholesale/order/quickorder_price_display_discounts'));
			if (is_array($data)) {
				foreach($data as $item) {
					if (!$item) continue;

					$pair = explode('=', $item);
					if (count($pair) < 2) continue;

					$customerGroupId = (int) $pair[0];
					$discountPercent = floatval($pair[1]);

					if (!$customerGroupId || !$discountPercent) continue;

					$this->_wholesalePriceDiscountDisplayRules[$customerGroupId] = $discountPercent;
				}
			}
		}

		return $this->_wholesalePriceDiscountDisplayRules;
	}



	public function calculateWholesalePrice($price)
	{
		$rules = $this->_getWholesalePriceDiscountDisplayRules();
		$groupId = self::getCustomer()->getGroupId();

		return (	Mage_Customer_Model_Group::NOT_LOGGED_IN_ID == $groupId
				&&	isset($rules[$groupId])
			)
			?	$price
			:	$price * (100 - $rules[$groupId]) / 100;
	}



	public static function value($value, $placeholder = '-')
	{
		return $value
			?	htmlspecialchars($value)
			:	$placeholder;
	}



	/*
	 * Formats date with Zend_Date format specified
	 */
	public static function getDateFormatted($date = null, $format = 'YYYY-MM-dd HH:mm:ss')
	{
		if(null === $date) {
			$date = time();
		} elseif(!is_int($date)) {
			$date = strtotime($date);
		}
		$localDate = Mage::app()->getLocale()->date($date, null, null);
		return $localDate->toString($format);
	}



	public function getDealerCornerUrl()
	{
		return Mage::getUrl('wholesale/dealer/corner', array('_secure' => true));
	}

}