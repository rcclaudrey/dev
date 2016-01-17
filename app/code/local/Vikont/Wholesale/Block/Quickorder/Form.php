<?php

class Vikont_Wholesale_Block_Quickorder_Form extends Mage_Core_Block_Template
{

	protected function _construct()
	{
		parent::_construct();
		$this->setTemplate('vk_wholesale/quickorder/form.phtml');
	}



	public function checkAvailability()
	{
		$messages = array();

		$customer = Mage::getSingleton('customer/session')->getCustomer();
		$address = $customer->getDefaultBillingAddress();

		if(!$address) {
			$messages[] = array(
				array('type' => 'plain',	'text' => $this->__('You need to set up a')),
				array('type' => 'red',		'text' => $this->__('default billing address')),
			);
		}

		if(!$customer->getDefaultShippingAddress()) {
			$messages[] = array(
				array('type' => 'plain',	'text' => $this->__('You need to set up a')),
				array('type' => 'red',		'text' => $this->__('default shipping address')),
			);
		}

		$data = Mage::helper('wholesale')->getQuickOrderFields();

		if(!$data['contact']) {
			$messages[] = array(
				array('type' => 'plain',	'text' => $this->__('Please specify your')),
				array('type' => 'red',		'text' => $this->__('name')),
				array('type' => 'plain',	'text' => $this->__('at')),
				array('type' => 'red',		'text' => $this->__('billing address')),
			);
		}

		if(!$data['company']) {
			$messages[] = array(
				array('type' => 'red',		'text' => $this->__('Company Name')),
				array('type' => 'plain',	'text' => $this->__('field must be populated for')),
				array('type' => 'red',		'text' => $this->__('billing address')),
			);
		}

		if(!$data['phone']) {
			$messages[] = array(
				array('type' => 'red',		'text' => $this->__('Telephone')),
				array('type' => 'plain',	'text' => $this->__('field must be populated for')),
				array('type' => 'red',		'text' => $this->__('billing address')),
			);
		}

		return $messages;
	}



	public function getFields()
	{
		$result = Mage::helper('wholesale')->getQuickOrderFields();

		$session = Mage::getSingleton('customer/session');
		$data = $session->getFormData();

		if($data && is_array($data)) {
			$result['rows'] = isset($data['rows']) ? $data['rows'] : array();
			$result['poNumber'] = isset($data['poNumber']) ? $data['poNumber'] : '';
			$result['notes'] = isset($data['notes']) ? $data['notes'] : '';
			$session->unsFormData();
		}

		return new Varien_Object($result);
	}

}