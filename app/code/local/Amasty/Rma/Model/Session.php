<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Rma
 */ 
    class Amasty_Rma_Model_Session extends Mage_Core_Model_Session_Abstract
    {
        protected $_order;
        
        public function __construct()
        {
            $namespace = 'amrma';
            
            $this->init($namespace);
        }
    
        public function login($username, $order)
        {   
            $request = Mage::getModel('amrma/request');
            
            if ($salesOrder = $request->authenticate($username, $order)) {
                $this->setOrder($salesOrder);
                $this->renewSession();
                return true;
            }
            return false;
        }
        
        public function loginByComment($comment){
            
            if ($salesOrder = $comment->authenticate()) {
                $this->setOrder($salesOrder);
                $this->renewSession();
                return true;
            }
            return false;
        }
        
        public function logout()
        {
            $this->setId(null);
            $this->getCookie()->delete($this->getSessionName());
            return $this;
        }
        
        public function setOrder($order){
            
            $this->setId($order->getId());
            $this->_order = $order;
        }
        
        public function getOrder(){
            return $this->_order;
        }
        
        public function isLoggedIn()
        {
            return (bool)$this->getId();
        }
    }
?>