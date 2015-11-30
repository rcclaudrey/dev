<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Rma
 */ 
    class Amasty_Rma_Block_Adminhtml_Request_Edit_Tab_Comment extends Mage_Adminhtml_Block_Widget_Form
    {
        protected $_request;
        
        public function __construct()
        {
            parent::__construct();
            $this->setTemplate('amasty/amrma/request/comment.phtml');
        }
        
        public function getRequest(){
            
            if (!$this->_request){
                $this->_request = Mage::registry('amrma_request');
            }
            
            return $this->_request;
        }


        public function getComments(){
            
            $request = $this->getRequest();
            
            $commentCollection = Mage::getModel('amrma/comment')->getCollection()
                    ->addFilter("request_id", $request->getId())
                    ->setOrder('created', 'desc');
            
            return $commentCollection;
        }
        
        public function getFiles($commentId){
            return Mage::getModel('amrma/file')->getCollection()
                        ->addFilter("comment_id", $commentId);
        }
        
        public function getStatuses(){
            $hlp = Mage::helper('amrma');
            return $hlp->getRequestStatuses();
        }
        
        public function getIsNotifyCustomer(){
            $hlp = Mage::helper('amrma');
            return $hlp->getIsNotifyCustomer();
        }
        
    }
?>