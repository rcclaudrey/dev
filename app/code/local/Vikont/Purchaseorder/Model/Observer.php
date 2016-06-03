<?php

class Vikont_Purchaseorder_Model_Observer
{

	public function payment_method_is_active($data)
	{
		if($data->getMethodInstance() instanceof Vikont_Purchaseorder_Model_Purchaseorder) {
			$result = false;

			if(Mage::app()->getStore()->isAdmin()) {
				$customer =  Mage::getSingleton('adminhtml/session_quote')->getCustomer();
			} else {
				$customer =  Mage::getSingleton('customer/session')->getCustomer();
			}

			$customerGroupId = $customer->getGroupId();

			$groupsAllowed = Mage::getStoreConfig('payment/purchaseorder/allowedgroups', $data['quote'] ? $data['quote']->getStoreId() : null);
			// by this, we don't break the functionality of the method if no group bas been selected
			// it is assumed that, if no customer groups should have access to the method,
			// it is more reasonable to just disable the whole method
			if($groupsAllowed) {
				$result = in_array($customerGroupId, explode(',', $groupsAllowed));
			} else {
				$result = true;
			}

			$data->getResult()->isAvailable = $result;
		}
	}

}