<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Rma
 */ 
class Amasty_Rma_GuestController extends Mage_Core_Controller_Front_Action
{
    public function loginAction(){
        
        if ($this->_getSession()->isLoggedIn())
            $this->_redirect('*/*/history');
        
        $this->loadLayout();
        $this->_initLayoutMessages('amrma/session');
        $this->renderLayout();
    }
    
    public function loginPostAction()
    {
        $session = $this->_getSession();
        $login = array();
        
        if ($this->getRequest()->isPost()) {
            $login = $this->getRequest()->getParam('login');
        } else {
            $login['username'] = $this->getRequest()->getParam('username');
            $login['order'] = $this->getRequest()->getParam('order');
        }
        
        if (!empty($login['username']) && !empty($login['order'])) {
            $this->_login($login);
        } else {
            $session->addError($this->__('Login and password are required.'));
        }
        
        
        if ($session->isLoggedIn()){
            $this->_redirect('*/*/history');
        } else {
            $backUrl = $this->_getRefererUrl();
            $this->_redirectUrl($backUrl);
        }
    }
    
    protected function _login($login){
        $session = $this->_getSession();
        
        try {
            $session->login($login['username'], $login['order']);
        } catch (Mage_Core_Exception $e) {
            $message = $e->getMessage();
            $session->addError($message);
            $session->setUsername($login['username']);
        }
    }
    
    public function logoutAction()
    {
        $this->_getSession()->logout();
        $this->_redirect('*/*/login');
    }
    
    public function historyAction()
    {
        $hlr = Mage::helper("amrma");
        
        if ($block = $this->getLayout()->getBlock('customer.account.link.back')) {
            $block->setRefererUrl($this->_getRefererUrl());
        }
        
        if ($hlr->getRequestsCount($this->_getSession()->getId()) > 0){
            $this->loadLayout();
            $this->_initLayoutMessages('amrma/session');
            $this->getLayout()->getBlock('head')->setTitle($this->__('RMA Order'));
            $this->renderLayout();
        } else {
            if (!$hlr->canCreateRma($this->_getSession()->getId())){
                
                Mage::getSingleton('core/session')->addError($hlr->getFailReason($this->_getSession()->getId()));
                
                $this->loadLayout();
                $this->_initLayoutMessages('amrma/session');
                $this->getLayout()->getBlock('head')->setTitle($this->__('RMA Order'));
                $this->renderLayout();

            } else {
                $this->_redirect('*/*/new', array('order_id' => $this->_getSession()->getId()));
            }
            
        }
    }
    
    public function viewAction()
    {
        $id    = (int)$this->getRequest()->getParam('id');
        
        if (!$this->_loadValidRequest($id)) {
            $this->_redirect('*/*/history');
            return;
        }
        
        $order = Mage::getModel('sales/order')->load(
            Mage::registry('amrma_request')->getOrderId()
        );
        
        Mage::register('amrma_order', $order);
        
        $this->loadLayout();
        $this->_initLayoutMessages('catalog/session');

        $this->getLayout()->getBlock('head')->setTitle($this->__('My RMA'));

        if ($block = $this->getLayout()->getBlock('customer.account.link.back')) {
            $block->setRefererUrl($this->_getRefererUrl());
        }
        
        $this->renderLayout();
    }
    
    public function editAction()
    {
        $this->_forward('form');
    }
    
    public function newAction()
    {
        $orderId    = (int)$this->getRequest()->getParam('order_id');

        $order = Mage::getModel('sales/order')->load($orderId);
        
        $hlr = Mage::helper("amrma");
        
        if ($this->_canViewOrder($order) && $hlr->canCreateRma($orderId)){
            
            $post = $this->getRequest()->getPost();
            
            if (($post)) {

                $pending = Amasty_Rma_Model_Status::getPendingStatus();
                
                $request = Mage::getModel('amrma/request');
                
                $request->setData(array(
                    'order_id' => $order->getId(),
                    'increment_id' => $order->getIncrementId(),
                    'store_id' => $order->getStoreId(),
                    'customer_id' => $order->getCustomerId(),
                    'email' => $order->getCustomerEmail(),
                    'customer_firstname' => $order->getCustomerFirstname(),
                    'customer_lastname' => $order->getCustomerLastname(),
                    'code' => uniqid(),
                    'status_id' => $pending->getId(),
                    'created' => Mage::getSingleton('core/date')->gmtDate(),
                    'updated' => Mage::getSingleton('core/date')->gmtDate(),
                    'items' => Mage::app()->getRequest()->getParam('items', array()),
                    'comment' => Mage::app()->getRequest()->getParam('comment', ''),
                    'field_1' => Mage::app()->getRequest()->getParam('field_1'),
                    'field_2' => Mage::app()->getRequest()->getParam('field_2'),
                    'field_3' => Mage::app()->getRequest()->getParam('field_3'),
                    'field_4' => Mage::app()->getRequest()->getParam('field_4'),
                    'field_5' => Mage::app()->getRequest()->getParam('field_5')
                )); 
                
                $request->save();
                $comment = $request->submitComment(FALSE, $_FILES['file']);
                $request->saveRmaItems();
                
                $request->sendNotificaitionRmaCreated($comment);
                
                $this->_forward('history');

            } else {
                $this->_forward('form');
            }
        } else {
            $error = $hlr->getFailReason($orderId);
            
            Mage::getSingleton('core/session')->addError($error);
            
            $this->_redirect('*/*/history');
        }
    }
    
    public function deleteAction()
    {
        $id    = (int)$this->getRequest()->getParam('id');
        
        if ($this->_loadValidRequest($id)) {
            $request = Mage::registry('amrma_request');
            $request->delete();
        } 
        
        $this->_redirect('*/*/history');
    }
    
    public function formAction()
    {
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $navigationBlock = $this->getLayout()->getBlock('customer_account_navigation');
        if ($navigationBlock) {
            $navigationBlock->setActive('amrma/customer/history');
        }
        $this->renderLayout();
    }
    
    protected function _getSession()
    {
        return Mage::getSingleton('amrma/session');
    }
    
    public function downloadAction()
    {
        $fileName = $this->getRequest()->getParam('file');
        
        $download = Amasty_Rma_Model_File::getUploadPath($fileName);

        if (is_writable($download)){
            $this->_prepareDownloadResponse($fileName, file_get_contents($download));
        } else {
            Mage::throwException('Unable read file');
        }

    }
    
    public function addCommentAction()
    {
        $id    = (int)$this->getRequest()->getParam('id');
        
        if (!$this->_loadValidRequest($id)) {
            $this->_redirect('*/*/history');
            return;
        }
        
        $model = Mage::registry('amrma_request');
        
        $data = $this->getRequest()->getPost();
        
        if ($data) {
	
            $model->setComment($data['comment']);
            
            try {
                
                $comment = $model->submitComment(FALSE, $_FILES['file']);
                
                $model->sendNotificaition2admin($comment);
                
                $model->setUpdated(Mage::getSingleton('core/date')->gmtDate());
                
                $model->save();
                
                $msg = Mage::helper('amrma')->__('Comment placed');
                
                Mage::getSingleton('core/session')->addSuccess($msg);

            } 
            catch (Exception $e) {
                
                Mage::getSingleton('core/session')->addError($e->getMessage());
            }	
            
            $this->_redirectReferer();
            return;
        }
    }
    
    protected function _canViewRequest($request)
    {
        $customerId = Mage::getSingleton('customer/session')->getCustomerId();
        
        if ($request->getId() && $request->getCustomerId() && ($request->getCustomerId() == $customerId)) {
            return true;
        }
        
        $amrma = $this->_getSession();
        
        if ($amrma){ //guest validation
            $salesOrder = Mage::getModel('sales/order')->load($amrma->getId());
            return $request->getEmail() == $salesOrder->getCustomerEmail();
        }
        return false;
    }
    
    protected function _canViewOrder($order)
    {
        $customerId = Mage::getSingleton('customer/session')->getCustomerId();
        
        if ($order->getId() && $order->getCustomerId() && ($order->getCustomerId() == $customerId)) {
            return true;
        }
        
        $session = $this->_getSession();
        
        if ($session){
            $sessionOrder = Mage::getModel('sales/order')->load($session->getId());
            
            return $order->getCustomerEmail() == $sessionOrder->getCustomerEmail();
        }
        return false;
    }

    protected function _loadValidRequest($entityId = null)
    {
        $request = Mage::getModel('amrma/request')->load($entityId);
        
        if ($this->_canViewRequest($request)) {
            Mage::register('amrma_request', $request);
            return true;
        } else {
            $amrma = $this->_getSession();
        
            if ($amrma){
                $this->_redirect('*/*/history');
            } else {
                $this->_redirect('*/*/login');
            }
        }
        return false;
    }
    
    
    public function exportAction(){
        $id    = (int)$this->getRequest()->getParam('id');
        $code = $this->getRequest()->getParam('code');
        
        if ($code){
           $request = Mage::getModel('amrma/request')->load($code, "code");
           Mage::register('amrma_request', $request); 
        } else {
            if (!$this->_loadValidRequest($id)) {
                $this->_redirect('*/*/history');
                return;
            }

            $_request = Mage::registry('amrma_request');

            if (!Mage::helper('amrma')->getIsAllowPrintLabel() ||
                    !$_request->allowPrintLabel()){
                throw Mage::exception('Mage_Core', Mage::helper('amrma')->__('Access denied.'));
            }
        }      
        $this->loadLayout();    
        $this->renderLayout();
    }
    
    
    public function confirmAction(){
        $id    = (int)$this->getRequest()->getParam('id');
        
        if (!$this->_loadValidRequest($id)) {
            $this->_redirect('*/*/history');
            return;
        }
        
        $_request = Mage::registry('amrma_request');
        
        if (!$_request->allowPrintLabel()){
            $this->_redirect('*/*/history');
            return;
        } else {
            $_request->setIsShipped(TRUE);
            $_request->save();
            
            $msg = Mage::helper('amrma')->__('Shipping confirmed');
                
            Mage::getSingleton('core/session')->addSuccess($msg);
                
            $this->_redirect('*/*/view', array("id" => $id));
        }
    }
    
    public function commentLookupAction(){
        $key = $this->getRequest()->getParam('key');
        $hlr = Mage::helper('amrma');
        $comment = Mage::getModel('amrma/comment')->load($key, 'unique_key');
        $session = $this->_getSession();
        
        if ($comment){
            
            try {
                $session->loginByComment($comment);

            } catch (Mage_Core_Exception $e) {
                $message = $e->getMessage();
                
                $session->addError($message);
            }
        } else {
            $session->addError($hlr->__("Wrong key"));
        }
        
        if ($session->isLoggedIn()){
            $url = Mage::getUrl('*/*/view', array(
                'id' => $comment->getRequestId(),
            ));
            $url .= '#comment_'.$comment->getId();
            
            $this->_redirectUrl($url);
        } else {
            $this->_redirect('amrma/guest/login');
        }   
    }
}