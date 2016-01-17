<?php

class Vikont_Pulliver_Block_Adminhtml_Sku_List extends Mage_Adminhtml_Block_Widget_Grid_Container
{

	public function __construct()
	{
		$this->_blockGroup = 'pulliver';
		$this->_controller = 'adminhtml_sku';
		$this->_headerText = Mage::helper('pulliver')->__('View SKU List');

		parent::__construct();

		$this->_removeButton('add');
	}

}