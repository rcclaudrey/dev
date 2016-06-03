<?php

class Vikont_Wholesale_Block_Application_Form extends Mage_Core_Block_Template
{

	protected function _construct()
	{
		parent::_construct();
		$this->setTemplate('vk_wholesale/application/form.phtml');
	}



	public function getCustomerAddresses()
	{
		$customer = Mage::getSingleton('customer/session')->getCustomer();
		$result = array();

		foreach ($customer->getAddresses() as $address) {
			$result[$address->getId()] = $address->format('oneline');
		}
		return $result;
	}



	public function getFields()
	{
		$result = Mage::helper('wholesale')->getDealerApplicationFields();

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