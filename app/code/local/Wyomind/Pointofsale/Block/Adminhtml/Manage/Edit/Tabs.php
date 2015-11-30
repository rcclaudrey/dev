<?php

class Wyomind_Pointofsale_Block_Adminhtml_Manage_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {

    public function __construct() {
        parent::__construct();
        $this->setId('pointofsale_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('pointofsale')->__(''));
    }

    protected function _beforeToHtml() {
        $this->addTab('informations', array(
            'label' => Mage::helper('pointofsale')->__('General Informations'),
            'title' => Mage::helper('pointofsale')->__('Store informations'),
            'content' => $this->getLayout()->createBlock('pointofsale/adminhtml_manage_edit_tab_informations')->toHtml(),
        ));
        $this->addTab('address', array(
            'label' => Mage::helper('pointofsale')->__('Address & hours'),
            'title' => Mage::helper('pointofsale')->__('Store address & contact'),
            'content' => $this->getLayout()->createBlock('pointofsale/adminhtml_manage_edit_tab_address')->toHtml(),
        ));
       
        $this->addTab('storeviews', array(
            'label' => Mage::helper('pointofsale')->__('Store Views Selection'),
            'title' => Mage::helper('pointofsale')->__('Store views'),
            'content' => $this->getLayout()->createBlock('pointofsale/adminhtml_manage_edit_tab_storeviews')->toHtml(),
        ));
        $this->addTab('customergroup', array(
            'label' => Mage::helper('pointofsale')->__('Customer Group Selection'),
            'title' => Mage::helper('pointofsale')->__('Customer Group'),
            'content' => $this->getLayout()->createBlock('pointofsale/adminhtml_manage_edit_tab_customergroup')->toHtml(),
        ));

        return parent::_beforeToHtml();
    }

}