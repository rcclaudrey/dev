<?php

class Vikont_OrdersGrid_Block_Adminhtml_Widget_Grid_Column_Renderer_Customer_Group
	extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
	protected $_options = false;


	protected function _getOptions()
	{
		if (!$this->_options) {
			$options = array();
			$options[0] = 'Guest';

			$customerGroups = Mage::getResourceModel('customer/group_collection')
				->addFieldToFilter('customer_group_id', array('gt' => 0))
				->load()
				->toOptionHash();

			$this->_options = array_merge($options,$customerGroups);
		}

		return $this->_options;
	}



	public function render(Varien_Object $row)
	{
		$value = $this->_getValue($row);
		$options = $this->_getOptions();

		return isset($options[$value])
			?	$options[$value]
			:	$value;
	}

}