<?php
    /**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Rma
 */ 
    class Amasty_Rma_Block_Rma_Edit extends Mage_Core_Block_Template
    {
        protected $_order;
        protected function _prepareLayout()
        {
            $hlr = Mage::helper('amrma');
            
            parent::_prepareLayout();
            $this->_order = Mage::getModel('sales/order');

            if ($id = $this->getRequest()->getParam('order_id')) {
                $this->_order->load($id);    
                Mage::register('current_order', $this->_order);
            }
            
            $this->setItems($hlr->getOrderItems($this->_order));
            
            $conditions = $hlr->getConditions();
            $resolutions = $hlr->getResolutions();
            $reasons = $hlr->getReasons();
            
            $this->setConditions(isset($conditions['values']) ? $conditions['values'] : $conditions);
            $this->setResolutions(isset($resolutions['values']) ? $resolutions['values'] : $resolutions);
            $this->setReasons(isset($reasons['values']) ? $reasons['values'] : $reasons);
        }
        
        public function getOrder(){
            return $this->_order;
        }
        
        public function getUploadUrl()
        {
            return $this->getUrl('*/*/upload');
        }
        
        public function getBackUrl()
        {
            return Mage::getUrl('*/*/history');
        }
        
        public function getIsEnablePerItem(){
            return Mage::helper("amrma")->getIsEnablePerItem();
        }

        public function hasExtraFields(){
            return Mage::helper("amrma")->hasExtraFields();
        }

        public function getExtraField($field){
            return Mage::helper("amrma")->getExtraField($field);
        }

        public function getExtraTitle(){
            return Mage::helper("amrma")->getExtraTitle();
        }
    }
?>