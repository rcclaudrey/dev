<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Acart
 */
class Amasty_Acart_Block_Adminhtml_Rule_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('ruleTabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('amacart')->__('Rule Configuration'));
    }

    protected function _beforeToHtml()
    {
        $tabs = array(
            'general'    => 'General',
            'stores'     => 'Stores & Customer Groups',
            'conditions' => 'Conditions',
            'schedule' => 'Schedule',
            'analytics' => 'Google Analytics',
            'test' => 'Test'
        );
        
        foreach ($tabs as $code => $label){
            
            $skip = !$this->getModel()->getId() && $code == 'test';
            
            if (!$skip){
            $label = Mage::helper('amacart')->__($label);
            
            $block = $this->getLayout()->createBlock('amacart/adminhtml_rule_edit_tab_' . $code);
            $block->setModel($this->getModel());
            
            $content = $block
                ->setTitle($label)
                ->toHtml();
            
            
                
            $this->addTab($code, array(
                'label'     => $label,
                'content'   => $content,
            ));
        }
        }
        
        return parent::_beforeToHtml();
    }
}