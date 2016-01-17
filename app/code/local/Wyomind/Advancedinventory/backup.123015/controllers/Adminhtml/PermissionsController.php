<?php

class Wyomind_Advancedinventory_Adminhtml_PermissionsController extends Mage_Adminhtml_Controller_Action {

    protected function _initAction() {
        $this->_title($this->__('Permissions'))->_title($this->__('POS / Warehouses '));
        $this->loadLayout()
                ->_setActiveMenu("sales");
        return $this;
    }

    public function indexAction() {

        $this->_initAction()
                ->renderLayout();
    }
	protected function _isAllowed() {
        return Mage::getSingleton('admin/session')->isAllowed('sales/pointofsale/permissions');
    }
    public function saveAction() {


        Mage::getConfig()->saveConfig("advancedinventory/setting/pos_permissions", $this->getRequest()->getParam('permissions'), "default", "0");
        Mage::getConfig()->cleanCache();

        die('{}');
    }

}
