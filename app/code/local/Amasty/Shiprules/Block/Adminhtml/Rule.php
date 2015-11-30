<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Shiprules
 */ 
class Amasty_Shiprules_Block_Adminhtml_Rule extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_rule';
        $this->_blockGroup = 'amshiprules';
        $this->_headerText = Mage::helper('amshiprules')->__('Rules');
        $this->_addButtonLabel = Mage::helper('amshiprules')->__('Add Rule');
        parent::__construct();
    }
}