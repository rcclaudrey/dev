<?php

class Vikont_OrdersGrid_Block_Adminhtml_Widget_Grid_Column_Customer_Group
	extends Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Select
{
	protected $_options = false;


	protected function _getOptions()
	{
		if (!$this->_options) {
			$options = array();

			$options[] = array(
				'value' =>  '',
				'label' =>  ''
			);

			$options[] = array(
				'value' =>  '0',
				'label' =>  'Guest'
			);

			$customerGroups = Mage::getResourceModel('customer/group_collection')
				->addFieldToFilter('customer_group_id', array('gt' => 0))
				->load()
				->toOptionArray();

			$this->_options = array_merge($options, $customerGroups);
		}

		return $this->_options;
	}

}