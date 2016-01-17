<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Acart
 */

/**
 * @author Amasty
 */   
class Amasty_Acart_Block_Adminhtml_Queue extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $format = Mage::app()->getLocale()->getDateTimeFormat(
            Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM
        );
        
        $data = Mage::app()->getLocale()
                ->date(
                    time(),
                    Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT. ' HH:mm:ss'))
            ->toString($format);
        
        $this->_controller = 'adminhtml_Queue';
        $this->_blockGroup = 'amacart';
        $this->_headerText = Mage::helper('amacart')->__('Queue'). ': '.$data;
//        $this->_addButtonLabel = Mage::helper('amacart')->__('Refresh');
//        $this->_addButtonLabel = Mage::helper('amacart')->__('Add Rule');
        
        $this->_addButton('refresh', array(
            'label'     =>  Mage::helper('amacart')->__('Refresh'),
            'onclick'   => 'document.location.href = \'' . Mage::helper("adminhtml")->getUrl('*/*/run') . '\'',
        ));
        
        parent::__construct();
        
        
        
        $this->_removeButton('add');
    }
    
}