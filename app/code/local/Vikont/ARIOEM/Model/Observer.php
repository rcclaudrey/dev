<?php

class Vikont_ARIOEM_Model_Observer
{

	public function controller_action_layout_render_before_adminhtml_customer_group_new($observer)
	{
		$layout = Mage::app()->getLayout();
		$group = Mage::registry('current_group');

		if(	!($groupBlock = $layout->getBlock('group'))
		||	!($formBlock = $groupBlock->getChild('form'))
		||	(Mage_Customer_Model_Group::NOT_LOGGED_IN_ID == $group->getId())
		) {
			return;
		}

		$helper = Mage::helper('arioem');

		$form = $formBlock->getForm();
		$form->setAction(Mage::helper('adminhtml')->getUrl('arioem/adminhtml_customer_group/save'));
		$fieldset = $form->addFieldset('arioem_fieldset', array('legend' => $helper->__('OEM Pricing')));
		$fieldset->addField('cost_percent', 'text',
            array(
                'name'  => 'cost_percent',
                'label' => $helper->__('Cost addition percentage'),
                'title' => $helper->__('Cost addition percentage'),
                'note'  => $helper->__('Amount of %% that must be added to product cost for customers belonging to this group'),
                'class' => 'validate-number',
				'value' => (float)$group->getData('cost_percent'),
            )
        );
	}

}