<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Rma
 */ 
class Amasty_Rma_Block_Adminhtml_Status_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('statusTabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('amrma')->__('Status Configuration'));
    }

    protected function _beforeToHtml()
    {
        $tabs = array(
            'general' => 'General',
            'labels' => 'Labels',
            'templates' => 'Email Templates'
        );
        
        foreach ($tabs as $code => $label){
            $label = Mage::helper('amrma')->__($label);
            
            $block = $this->getLayout()->createBlock('amrma/adminhtml_status_edit_tab_' . $code);
            $block->setModel($this->getModel());
            
            $content = $block
                ->setTitle($label)
                ->toHtml();
            
            
                
            $this->addTab($code, array(
                'label'     => $label,
                'content'   => $content,
            ));
        
        }
        
        return parent::_beforeToHtml();
    }
}