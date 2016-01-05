<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Acart
 */

/**
 * @author Amasty
 */   
class Amasty_Acart_Block_Adminhtml_Rule extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_rule';
        $this->_blockGroup = 'amacart';
        $this->_headerText = Mage::helper('amacart')->__('Rules');
        $this->_addButtonLabel = Mage::helper('amacart')->__('Add Rule');
        parent::__construct();
    }
}