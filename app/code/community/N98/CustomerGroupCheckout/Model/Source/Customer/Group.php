<?php

class N98_CustomerGroupCheckout_Model_Source_Customer_Group
{
	/**
	 * Customer groups options array
	 *
	 * @var null|array
	 */
	protected $_options;

	/**
	 * Retrieve customer groups as array
	 *
	 * @return array
	 */
	public function toOptionArray()
	{
		if (!$this->_options) {
			$this->_options = Mage::getResourceModel('customer/group_collection')
				->setRealGroupsFilter()
				->loadData()
				->toOptionArray();

//			array_unshift($this->_options, array(
//					'value'=> Mage_Customer_Model_Group::NOT_LOGGED_IN_ID,
//					'label'=> 'NOT LOGGED IN',
//				));

			array_unshift($this->_options, array(
					'value'=> '',
					'label'=> Mage::helper('adminhtml')->__('ALL GROUPS')
				));
		}
		return $this->_options;
	}

}