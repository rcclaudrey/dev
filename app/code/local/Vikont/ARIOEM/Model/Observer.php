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



	public function customer_login($observer)
	{
		$customer = $observer->getCustomer();

		$customerCostPercent = 0;
		$isWholesale = false;
		$customerGroupId = $customer->getGroupId();

		if (Mage::helper('core')->isModuleEnabled('Vikont_Wholesale')) {
			if (Vikont_Wholesale_Model_Source_Dealer_Status::APPROVED == $customer->getDealerStatus()) {
				$isWholesale = true;

				$customerCostPercent = floatval($customer->getDealerCost());
				if (!$customerCostPercent) {
					$customerGroup = Mage::getModel('customer/group')->load($customerGroupId);
					$customerCostPercent = $customerGroup->getId()
						?	floatval($customerGroup->getCostPercent())
						:	0;
				}
			}
		}

		$_SESSION['customer_base']['cost_percent'] = $customerCostPercent;
		$_SESSION['customer_base']['is_wholesale'] = $isWholesale;
		$_SESSION['customer_base']['group_id'] = $customerGroupId;
	}



	public function customer_logout($observer)
	{
		$_SESSION['customer_base']['is_wholesale'] = false;
		$_SESSION['customer_base']['cost_percent'] = 0;
		$_SESSION['customer_base']['group_id'] = Mage_Customer_Model_Group::NOT_LOGGED_IN_ID;
	}



	public function sales_order_shipment_save_before($observer)
	{
		$shipment = $observer->getShipment();

		if (!$shipment->getId()) { // this is a new shipment
			$brandOptionId  = Mage::getStoreConfig('arioem/add_to_cart/dummy_product_brand_option_id');
			$partNumberOptionId  = Mage::getStoreConfig('arioem/add_to_cart/dummy_product_partNo_option_id');

			$oemParts = array();

			foreach($shipment->getItemsCollection() as $item) {
				$productOptions = $item->getOrderItem()->getProductOptions();

				if (isset($productOptions['info_buyRequest']['options'])) {
					$options = $productOptions['info_buyRequest']['options'];

					$brandName = $options[$brandOptionId];
					$brandCode = Vikont_ARIOEM_Model_Source_Oembrand::getOptionCode($brandName);

					$partNumber = $options[$partNumberOptionId];

					$oemParts[$brandCode][$partNumber] = $item->getQty();
				}
			}

			Mage::helper('arioem/OEM')->decreaseInventoryValues($oemParts);
		}
	}

}