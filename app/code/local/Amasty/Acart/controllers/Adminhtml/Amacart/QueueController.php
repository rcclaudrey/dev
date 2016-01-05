<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Acart
 */
class Amasty_Acart_Adminhtml_Amacart_QueueController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $hlr = Mage::helper('amacart');
        
        $this->_run();
        
        Mage::getSingleton('adminhtml/session')->addNotice('If there are no emails in the queue for a long time, please make sure that cron is setup for your Magento. Please read this for more information: <a target="_blank" href="http://support.amasty.com/index.php?/Knowledgebase/Article/View/72/24/magento-cron">' . ($hlr->__("here")) . '</a>');
        
        $this->loadLayout(); 
        
        $this->_setActiveMenu('promo/amacart/queue');
        
        $this->_addContent($this->getLayout()->createBlock('amacart/adminhtml_queue')); 
        $this->renderLayout();

    }
    
    public function massCancelAction(){
        $ids = $this->getRequest()->getParam('queue');
        
        if (is_array($ids) && count($ids) > 0){
            
            Mage::getModel('amacart/schedule')->massCancel($ids);
            
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('amacart')->__('Actions has been canceled.'));
            $this->_redirect('*/*/index');
            
        }
    }
    
    protected function _run(){
        Mage::getModel('amacart/schedule')->run();
    }


    public function runAction(){
        $this->_run();
        
//        $msg = Mage::helper('amacart')->__('Process has been successfully runned.');
//                
//        Mage::getSingleton('adminhtml/session')->addSuccess($msg);
        
        $this->_redirect('*/*/index');
        
    }
    
    public function editAction(){
        
        $id = $this->getRequest()->getParam('id');
        $history = Mage::getModel('amacart/history')->load($id);
        if ($history->getId()){
            
            $this->loadLayout();
            
            $this->_setActiveMenu('promo/amacart/history');
            
            $editBlock = $this->getLayout()->createBlock('amacart/adminhtml_queue_edit');
            $editBlock->setModel($history);
            
            $this->_addContent($editBlock);
            
            $this->renderLayout();
        } else {
            $this->_getSession()->addError($this->__('This history no longer exists.'));
            $this->_redirect('*/*/');
        }
    }
    
    public function saveAction(){
        $data = $this->getRequest()->getPost();
        $model  = Mage::getModel('amacart/history');
        $id     = $this->getRequest()->getParam('id');
        
        if ($data) {
            $model->setData($data);
            
            $model->setId($id);
            
            try {

                $model->save();

                Mage::getSingleton('adminhtml/session')->setFormData(false);

                $msg = Mage::helper('amacart')->__('History item has been successfully saved');
                
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
        }
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('promo/amacart');
    }
}
?>