<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Rma
 */ 
class Amasty_Rma_Block_Adminhtml_Status extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_status';
        $this->_blockGroup = 'amrma';
        $this->_headerText = Mage::helper('amrma')->__('Status Management');
        $this->_addButtonLabel = Mage::helper('amrma')->__('Add Status');
        parent::__construct();
    }
}