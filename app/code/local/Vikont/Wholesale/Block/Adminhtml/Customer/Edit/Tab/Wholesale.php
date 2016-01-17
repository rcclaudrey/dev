<?php

class Vikont_Wholesale_Block_Adminhtml_Customer_Edit_Tab_Wholesale
	extends Mage_Adminhtml_Block_Template
	implements Mage_Adminhtml_Block_Widget_Tab_Interface
{

    public function _construct()
    {
		parent::_construct();
		$this->setTemplate('vk_wholesale/customer/edit/tab/wholesale.phtml');
    }


	public function getTabLabel()	{	return $this->__('Dealer Information');	}

    public function getTabTitle()	{	return $this->__('Dealer Information');	}

	public function canShowTab()	{	return (boolean) Mage::registry('current_customer')->getId();	}

	public function isHidden()		{	return !(boolean) Mage::registry('current_customer')->getId();	}

	public function getAfter()		{	return 'tags';	}



	public function getApplicationHtml()
	{
		return $this->getLayout()
				->createBlock('wholesale/adminhtml_customer_edit_tab_wholesale_application')
					->toHtml();
	}



	public function getDealerHtml()
	{
		return $this->getLayout()
				->createBlock('wholesale/adminhtml_customer_edit_tab_wholesale_dealer')
					->toHtml();
	}

}