<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Shiprules
 */ 
class Amasty_Shiprules_Block_Adminhtml_Rule_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('ruleTabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('amshiprules')->__('Rule Configuration'));
    }

    protected function _beforeToHtml()
    {
        $tabs = array(
            'general'    => 'General',
            'products'   => 'Products',
            'rates'      => 'Rates',
            'conditions' => 'Address Conditions',
            'stores'     => 'Stores & Customer Groups',
        );
        
        foreach ($tabs as $code => $label){
            $label = Mage::helper('amshiprules')->__($label);
            $content = $this->getLayout()->createBlock('amshiprules/adminhtml_rule_edit_tab_' . $code)
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