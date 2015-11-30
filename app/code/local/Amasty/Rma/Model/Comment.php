<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Rma
 */ 
class Amasty_Rma_Model_Comment extends Mage_Core_Model_Abstract
{
    protected $_files = array();
    
    public function _construct()
    {
        $this->_init('amrma/comment');
    }   
    
    public function addFile($file){
        $this->_files[] = $file;
    }
    
    public function getFiles(){
        return $this->_files;
    }
    
    public function getFirstFileUrl(){
        $ret = null;
        if (isset($this->_files[0])){
            $ret = $this->_files[0]->getHref();
        }
        
        return $ret;
    }
    
    public function getFirstFileName(){
        $ret = null;
        if (isset($this->_files[0])){
            $ret = $this->_files[0]->getName();
        }
        
        return $ret;
    }
    
    public function getCommentText(){
        return nl2br($this->getCommentValue());
    }
    
    public function getCommentUrl(){
        return Mage::getUrl('amrmafront/guest/commentLookup', array(
            'key' => $this->getUniqueKey()
        ));
    }
    
    public function authenticate(){
        $ret = NULL;
        
        if ($this->getId()){
            $request = Mage::getModel('amrma/request')->load($this->getRequestId());
            $salesOrder = Mage::getModel('sales/order')->load($request->getOrderId());
            
            if ($salesOrder->getId()){
                $ret = $salesOrder;
            } else {
                throw Mage::exception('Mage_Core', Mage::helper('amrma')->__('Order expired'));
            }
        } else {
            throw Mage::exception('Mage_Core', Mage::helper('amrma')->__('Wrong key'));
        }
        return $ret;
    }
    
}