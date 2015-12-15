<?php

class Wyomind_Watchlog_Adminhtml_AdvancedController extends Mage_Adminhtml_Controller_Action {

    protected function _initAction() {

        Mage::helper('watchlog')->checkWarning();
        
        $this->loadLayout()->_setActiveMenu("watchlog/watchlog")->_addBreadcrumb(Mage::helper("adminhtml")->__("Watchlog"), Mage::helper("adminhtml")->__("Watchlog"));
        return $this;
    }
	protected function _isAllowed() {
        return Mage::getSingleton('admin/session')->isAllowed('system/watchlog');
    }
    public function indexAction() {
        $this->_title($this->__("Watchlog"));
        $this->_title($this->__("Manager Watchlog"));

        $this->_initAction();
        $this->renderLayout();
    }

    public function purgeAction() {
        $log = Mage::helper('watchlog')->purgeData();
        $this->_redirect('*/*');
    }

}
