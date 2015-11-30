<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Rma
 */ 
class Amasty_Rma_Block_Adminhtml_Request_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('requestTabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('amrma')->__('Request Configuration'));
    }

    protected function _beforeToHtml()
    {
        $tabs = array(
            'request' => 'Request',
            'items' => 'RMA Items',
            'notes' => 'Notes'
        );
        
        foreach ($tabs as $code => $label){
            $label = Mage::helper('amrma')->__($label);
            
            $block = $this->getLayout()->createBlock('amrma/adminhtml_request_edit_tab_' . $code);
            $block->setModel($this->getModel());
            
            $content = $block
                ->setTitle($label)
                ->toHtml();
            
            
            if ($code == 'request'){
                $commentBlock = $this->getLayout()->createBlock('amrma/adminhtml_request_edit_tab_comment');
                $commentBlock->setModel($this->getModel());

                $content .= $commentBlock
                    ->setTitle($label)
                    ->toHtml();
            }
            
                
            $this->addTab($code, array(
                'label'     => $label,
                'content'   => $content,
            ));
        
        }
        
        return parent::_beforeToHtml();
    }
}