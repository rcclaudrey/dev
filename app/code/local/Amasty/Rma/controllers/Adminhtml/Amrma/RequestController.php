<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Rma
 */ 
class Amasty_Rma_Adminhtml_Amrma_RequestController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->_title($this->__('Request Management'));
        
        $this->loadLayout(); 
        $this->_setActiveMenu('sales/amrma/request');
        $this->_addContent($this->getLayout()->createBlock('amrma/adminhtml_request')); 
        $this->renderLayout();
    }
    
    public function editAction() 
    {
        $id     = (int) $this->getRequest()->getParam('id');
        
        
        $model  = Mage::getModel('amrma/request')->load($id);

        if ($id && !$model->getId()) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('amrma')->__('Record does not exist'));
            $this->_redirect('*/*/');
        } else {

//            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
//            
//            if (!empty($data)) {
//            
//                $model->setData($data);
//            }
//            else 
//            {
//                $this->prepareForEdit($model);
//            }
        
            $this->loadLayout();

            $this->_setActiveMenu('sales/amrma/request');
            
//            $this->_setActiveMenu('sales/amrma/' . $this->_modelName . 's');
            $this->_title($this->__('Edit RMA Request'));

            $head = $this->getLayout()->getBlock('head');
            $head->setCanLoadExtJs(1);
            $head->setCanLoadStatussJs(1);
            
            
            
            Mage::register('amrma_request', $model);
            
            $editBlock = $this->getLayout()->createBlock('amrma/adminhtml_request_edit');
            $tabsBlock = $this->getLayout()->createBlock('amrma/adminhtml_request_edit_tabs');
            
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

        $hlr = Mage::helper('amrma');
        $id     = $this->getRequest()->getParam('id');
        $model  = Mage::getModel('amrma/request')->load($id);
        $data = $this->getRequest()->getPost();
        
        if ($data) {
	
            try {
                $model->setUpdated(Mage::getSingleton('core/date')->gmtDate());
                        
                if ($this->getRequest()->getParam('comment_submit')){
                    
                    $statusChanged = $model->getStatusId() != $this->getRequest()->getParam('status_id');
                    
                    $model->addData(array(
                        'comment' => $this->getRequest()->getParam('comment'),
                        'status_id' => $this->getRequest()->getParam('status_id'),
                    ));
                    
                    $model->save();
                    
                    $comment = $model->submitComment(TRUE, $_FILES['file']);
                    
                    Mage::getSingleton('adminhtml/session')->addSuccess($hlr->__('Comment saved'));
                    
                    if ($this->getRequest()->getParam('is_customer_notified')){
                        if ($model->sendNotificaition($comment, $statusChanged))
                            Mage::getSingleton('adminhtml/session')->addSuccess($hlr->__('Notification has been sent'));
                    }
                    
                } else {
                    $model->addData($data);
                    $model->setId($id);
                    $model->save();
                    $model->updateItemsQty();
                    
                    Mage::getSingleton('adminhtml/session')->addSuccess($hlr->__('Request has been successfully saved'));
                }
                
                Mage::getSingleton('adminhtml/session')->setFormData(false);

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
    }
    
    public function allowAction() 
    {
        $id     = $this->getRequest()->getParam('id');
        $model  = Mage::getModel('amrma/request')->load($id);
        if ($model){
            $model->setAllowCreateLabel(!$model->getAllowCreateLabel());
            $model->save();
            
            $this->_redirect('*/*/edit', array('id' => $model->getId()));
        }
        else {
            $this->_redirect('*/*');
        }
    }
    
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('sales/amrma');
    }
}
?>