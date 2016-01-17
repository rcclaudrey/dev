<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Acart
 */

/**
 * @author Amasty
 */   
class Amasty_Acart_Block_Adminhtml_History extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $schedule = Mage::getModel('amacart/schedule');
        
        $this->_controller = 'adminhtml_History';
        $this->_blockGroup = 'amacart';
        $this->_headerText = Mage::helper('amacart')->__('History');
//        $this->_addButtonLabel = Mage::helper('amacart')->__('Add Rule');
        
        parent::__construct();
        
        
        
        $this->_removeButton('add');
    }
    
}