<?php

class Wyomind_Watchlog_Adminhtml_BasicController extends Mage_Adminhtml_Controller_Action {

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

    public function deleteAction() {
        if ($this->getRequest()->getParam("id") > 0) {
            try {
                $model = Mage::getModel("watchlog/watchlog");
                $model->setId($this->getRequest()->getParam("id"))->delete();
                Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("Item was successfully deleted"));
                $this->_redirect("*/*/");
            } catch (Exception $e) {
                Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
                $this->_redirect("*/*/edit", array("id" => $this->getRequest()->getParam("id")));
            }
        }
        $this->_redirect("*/*/");
    }

    public function massRemoveAction() {
        try {
            $ids = $this->getRequest()->getParams('watchlolg_ids', array());
            foreach ($ids['watchlog_ids'] as $id) {
                $model = Mage::getModel("watchlog/watchlog");
                $model->load($id)->delete();
            }
            Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("Item(s) was successfully removed"));
        } catch (Exception $e) {
            Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
        }
        $this->_redirect('*/*/');
    }

}
