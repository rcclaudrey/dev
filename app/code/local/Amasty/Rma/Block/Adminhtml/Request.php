<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Rma
 */ 
class Amasty_Rma_Block_Adminhtml_Request extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_request';
        $this->_blockGroup = 'amrma';
        $this->_headerText = Mage::helper('amrma')->__('Request Management');
        
        parent::__construct();
        
        $this->_removeButton("add");
    }
}