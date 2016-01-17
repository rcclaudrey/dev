<?php

class Vikont_Wholesale_Block_Adminhtml_Customer_Edit_Tab_Wholesale_Dealer extends Mage_Adminhtml_Block_Widget_Form
{

	public function getCustomer()
	{
		return Mage::registry('current_customer');
	}



    protected function _prepareForm()
    {
		$customer = Mage::registry('current_customer');

		$form = new Varien_Data_Form();
		$form->setHtmlIdPrefix('_account');
		$form->setFieldNameSuffix('account');


		$customerForm = Mage::getModel('customer/form');
        $customerForm
			->setEntity($customer)
            ->setFormCode('wholesale')
            ->initDefaultValues();

        $attributes = $customerForm->getAttributes();
        foreach ($attributes as $attribute) {
            $attribute->setFrontendLabel(Mage::helper('customer')->__($attribute->getFrontend()->getLabel()));
            $attribute->unsIsVisible();
        }

		$fieldset = $form->addFieldset('dealer_fieldset', array(
			'legend' => Mage::helper('customer')->__('Dealer Information')
		));

		$this->_setFieldset($attributes, $fieldset, array('application'));

/**
		if(Vikont_Wholesale_Model_Source_Dealer_Status::CANDIDATE == $customer->getData(Vikont_Wholesale_Helper_Data::ATTR_DEALER_STATUS)) {

			$buttonsHtml = $this->getLayout()->createBlock('adminhtml/template')
					->setTemplate('vk_wholesale/customer/edit/tab/wholesale/action.phtml')
					->toHtml();

			$form->getElement('dealer_status')
					->setAfterElementHtml($buttonsHtml);
		} /**/

		$notifyCheckboxHtml = '<div class="wsa-customer-notifycustomer-container"><input type="checkbox" name="account[dealer_status_customer_notify]" checked="checked" value="1" id="dealer_status_customer_notify"><label for="dealer_status_customer_notify">'.$this->__('Notify customer on dealer status changed').'</label></div>';

		$form->getElement('dealer_status')
				->setAfterElementHtml($notifyCheckboxHtml);

		$form->getElement('dealer_cost')
				->addClass('validate-number')
				->setNote($this->__('A percentage value over the cost. If not set, the value from customer group will be taken'));

/**
// just another way of adding fields. Magento is diverse!
		$fieldset->addField('dealer_status', 'select', array(
				'label' => Mage::helper('customer')->__('Dealer status'),
				'name'  => Vikont_Wholesale_Helper_Data::ATTR_DEALER_STATUS,
				'values' => Mage::getModel('wholesale/source_dealer_status')->toShortOptionArray(),
//				'value' => $customer->getData(Vikont_Wholesale_Helper_Data::ATTR_DEALER_STATUS),
			));

		$fieldset->addField('dealer_cost', 'text', array(
				'label' => Mage::helper('customer')->__('Dealer cost'),
				'name'  => Vikont_Wholesale_Helper_Data::ATTR_DEALER_COST,
				'required' => true,
				'class' => 'validate-number',
				'note' => $this->__('A percentage value over the cost. If not set, a customer group setting will be taken'),
//				'value' => $customer->getData(Vikont_Wholesale_Helper_Data::ATTR_DEALER_COST),
			));
/**/

		$form->setValues($customer->getData());
		$this->setForm($form);
		return $this;
    }

}