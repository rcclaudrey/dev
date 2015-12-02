<?php

class Vikont_Wholesale_Adminhtml_IndexController extends Mage_Adminhtml_Controller_Action
{

	public function approveAction()
	{
		$customerId = $this->getRequest()->getParam('id');

		if($customerId) {
			$customer = Mage::getModel('customer/customer')->load($customerId);
		}

		if(!$customerId || !$customer->getId() !== $customerId) {
			$$this->_getSession()->addError($this->__('No such customer'));
			$this->_redirect('adminhtml/customer');
			return;
		}

		$customer
			->setData(Vikont_Wholesale_Helper_Data::ATTR_DEALER_STATUS, Vikont_Wholesale_Model_Source_Dealer_Status::APPROVED)
			->save();

		$$this->_getSession()->addSuccess($this->__('Dealer application from customer %s <%s> has been approved', $customer->getName(), $customer->getEmail()));

		$this->_redirect('adminhtml/customer/edit', array('id' => $customerId));
	}



	public function declineAction()
	{
		$customerId = $this->getRequest()->getParam('id');

		if($customerId) {
			$customer = Mage::getModel('customer/customer')->load($customerId);
		}

		if(!$customerId || !$customer->getId() !== $customerId) {
			$$this->_getSession()->addError($this->__('No such customer'));
			$this->_redirect('adminhtml/customer');
			return;
		}

		$customer
			->setData(Vikont_Wholesale_Helper_Data::ATTR_DEALER_STATUS, Vikont_Wholesale_Model_Source_Dealer_Status::DECLINED)
			->save();

		$$this->_getSession()->addSuccess($this->__('Dealer application from customer %s <%s> has been declined', $customer->getName(), $customer->getEmail()));

		$this->_redirect('adminhtml/customer/edit', array('id' => $customerId));
	}

}