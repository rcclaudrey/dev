<?php

class Wyomind_Advancedinventory_Adminhtml_LogController extends Mage_Adminhtml_Controller_Action {

    protected function _initAction() {
        $this->_title($this->__('Journal'))->_title($this->__('Stock movement'));
        $this->loadLayout()
                ->_setActiveMenu("sales/log");
        return $this;
    }

    public function indexAction() {

        $this->_initAction()
                ->renderLayout();
    }
	protected function _isAllowed() {
        return Mage::getSingleton('admin/session')->isAllowed('sales/pointofsale/log');
    }
    public function massDeleteAction() {


        if ($ids = $this->getRequest()->getParam('ids')) {


            try {
                foreach ($ids as $id) {

                    $model = Mage::getModel('advancedinventory/log');
                    $model->setId($id);
                    $model->delete();


                    $this->_redirect('*/*/');
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('advancedinventory')->__('Log deleted.'));
                return;
            } catch (Exception $e) {

                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());

                $this->_redirect('*/*/');
                return;
            }
        }
        $this->_redirect('*/*/');
    }

}
