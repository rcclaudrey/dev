<?php

class Wyomind_Advancedinventory_Block_Adminhtml_Stocks extends Mage_Adminhtml_Block_Widget_Grid_Container {

    public function __construct() {

        $this->_controller = 'adminhtml_stocks';

        $this->_blockGroup = 'advancedinventory';

        $this->_headerText = Mage::helper('advancedinventory')->__('Manage product stocks');


        $permissions = Mage::helper('advancedinventory/permissions')->getUserPermissions();
        $all = $permissions->isAdmin();

        $this->_addButton('save', array(
            'label' => Mage::helper('advancedinventory')->__('Save all changes'),
            'class' => 'save',
            'onclick' => "InventoryManager.saveStocks('" . $this->getUrl('*/*/save', array('data' => 'all', "is_admin" => (int) $all, "store_id" => Mage::app()->getRequest()->getParam('store', 0))) . "','all')",
        ));

        $this->_addButton('reset', array(
            'label' => Mage::helper('advancedinventory')->__('Reset'),
            'class' => 'delete',
            'onclick' => "setLocation('" . $this->getUrl('*/*/index') . "')"
        ));



        parent::__construct();
        $this->setTemplate('stocks/container.phtml');
        $this->removeButton('add');
    }

    public function isSingleStoreMode() {
        if (!Mage::app()->isSingleStoreMode()) {
            return false;
        }
        return true;
    }

}
