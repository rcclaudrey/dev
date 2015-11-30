<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Rma
 */ 
class Amasty_Rma_Adminhtml_Amrma_StatusController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->loadLayout(); 
        $this->_setActiveMenu('sales/amrma/status');
        $this->_addContent($this->getLayout()->createBlock('amrma/adminhtml_status')); 
        $this->renderLayout();
    }
    
    public function newAction() 
    {
        $this->editAction();
    }
    
    public function deleteAction() 
    {
        $id     = (int) $this->getRequest()->getParam('id');
        $model  = Mage::getModel('amrma/status')->load($id);

        if ($model->getId()) {
            $model->delete();
            $msg = Mage::helper('amrma')->__('Status has been successfully deleted');
                
            Mage::getSingleton('adminhtml/session')->addSuccess($msg);
            $this->_redirect('*/*/');
        }
    }
	
    public function editAction() 
    {
        $id     = (int) $this->getRequest()->getParam('id');
        $model  = Mage::getModel('amrma/status')->load($id);

        if ($id && !$model->getId()) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('amrma')->__('Record does not exist'));
            $this->_redirect('*/*/');
        } else {

            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            
            if (!empty($data)) {
                $model->setData($data);
            }
            else 
            {
                $this->prepareForEdit($model);
            }

            $this->loadLayout();

            $this->_setActiveMenu('sales/amrma/status');
            
//            $this->_setActiveMenu('sales/amrma/' . $this->_modelName . 's');
            $this->_title($this->__('Edit'));

            $head = $this->getLayout()->getBlock('head');
            $head->setCanLoadExtJs(1);
            $head->setCanLoadStatussJs(1);
            
            Mage::register('amrma_status', $model);
            
            $editBlock = $this->getLayout()->createBlock('amrma/adminhtml_status_edit');
            $tabsBlock = $this->getLayout()->createBlock('amrma/adminhtml_status_edit_tabs');
            
            $editBlock->setModel($model);
            $tabsBlock->setModel($model);
            
            
            $this->_addContent($editBlock);
            $this->_addLeft($tabsBlock);

            $this->renderLayout();
        }
    }
    
    protected function prepareForEdit($model)
    {
        
    }
    
    public function saveAction() 
    {
        $id     = $this->getRequest()->getParam('id');
        $model  = Mage::getModel('amrma/status');
        $data = $this->getRequest()->getPost();
        
        if ($data) {
	
            $model->setData($data);  // common fields

            $model->setId($id);
            try {
                $this->prepareForSave($model);

                $model->save();

                Mage::getSingleton('adminhtml/session')->setFormData(false);

                $msg = Mage::helper('amrma')->__('Status has been successfully saved');
                
                Mage::getSingleton('adminhtml/session')->addSuccess($msg);
                if ($this->getRequest()->getParam('continue')){
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                }
                else {
                    $this->_redirect('*/*');
                }
            } 
            catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $id));
            }	
            return;
        }
        
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('amrma')->__('Unable to find a record to save'));
        $this->_redirect('*/*');
	
    }
    
    public function prepareForSave($model)
    {
        return true;
    }
    
    protected function getMassActionIds(){
        $ids = $this->getRequest()->getParam('statuses');
        if (!is_array($ids)) {
             Mage::getSingleton('adminhtml/session')->addError(Mage::helper('amrma')->__('Please select records'));
             $this->_redirect('*/*/');
             return;
        }
        return $ids;
    }

    public function massInactivateAction(){
        
        $ids = $this->getMassActionIds();
        
        try {
            foreach ($ids as $id) {
                $model = Mage::getModel('amrma/status')->load($id);
                $model->addData(array(
                    'is_active' => 0
                ));
                $model->save();
            }
            
            Mage::getSingleton('adminhtml/session')->addSuccess(
                Mage::helper('adminhtml')->__(
                    'Total of %d record(s) were successfully updated', count($ids)
                )
            );
        } 
        catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
        
        $this->_redirect('*/*/');
    }
    
    public function massActivateAction(){
        
        $ids = $this->getMassActionIds();
        
        try {
            foreach ($ids as $id) {
                $model = Mage::getModel('amrma/status')->load($id);
                $model->addData(array(
                    'is_active' => 1
                ));
                $model->save();
            }
            
            Mage::getSingleton('adminhtml/session')->addSuccess(
                Mage::helper('adminhtml')->__(
                    'Total of %d record(s) were successfully updated', count($ids)
                )
            );
        } 
        catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
        
        $this->_redirect('*/*/');
    }
    
    public function massDeleteAction(){
        
        $ids = $this->getMassActionIds();
        
        try {
            foreach ($ids as $id) {
                $model = Mage::getModel('amrma/status')->load($id);
                if (!$model->status_key){
                    $model->delete();
                }
            }
            
            Mage::getSingleton('adminhtml/session')->addSuccess(
                Mage::helper('adminhtml')->__(
                    'Total of %d record(s) were successfully deleted', count($ids)
                )
            );
        } 
        catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
        
        $this->_redirect('*/*/');
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('sales/amrma');
    }
}
?>