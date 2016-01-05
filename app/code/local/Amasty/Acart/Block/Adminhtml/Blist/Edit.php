<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Acart
 */

/**
 * @author Amasty
 */ 
class Amasty_Acart_Block_Adminhtml_Blist_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id'; 
        $this->_blockGroup = 'amacart';
        $this->_controller = 'adminhtml_blist';
    }

    public function getHeaderText()
    {
        return Mage::helper('amacart')->__('Blocked Recipient');
    }
}