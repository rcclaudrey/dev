<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Rma
 */ 
class Amasty_Rma_Block_Guest_View extends Mage_Core_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('amasty/amrma/guest/view.phtml');
        
        $this->setRmaRequest(Mage::registry('amrma_request'));
        $this->setRmaOrder(Mage::registry('amrma_order'));
        
        $collection = Mage::getResourceModel('amrma/item_collection')
            ->addFilter('request_id', $this->getRmaRequest()->getId())
        ;

        $this->setItems($collection);   
        
        $comments = Mage::getResourceModel('amrma/comment_collection')
            ->addFilter('request_id', $this->getRmaRequest()->getId())
            ->setOrder('created', 'desc');
        $this->setComments($comments);
    }
    
    public function getOrderUrl($request)
    {
        return $this->getUrl('sales/order/view/', array('order_id' => $request->getOrderId()));
    }
    
    public function getAddress()
    {
        return  Mage::helper('amrma')->getReturnAddress();
    }
    
    public function getShippingConfirmation(){
        return  Mage::helper('amrma')->getShippingConfirmation();
    }
    
    public function getIsAllowPrintLabel(){
        return  Mage::helper('amrma')->getIsAllowPrintLabel();
    }
    
    public function getCustomerName()
    {
        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            return Mage::helper('customer')->getCustomerName();
        } else {
            $billingAddress = Mage::registry('amrma_order')->getBillingAddress();

            $name = '';
            $config = Mage::getSingleton('eav/config');
            if ($config->getAttribute('customer', 'prefix')->getIsVisible() && $billingAddress->getPrefix()) {
                $name .= $billingAddress->getPrefix() . ' ';
            }
            $name .= $billingAddress->getFirstname();
            if ($config->getAttribute('customer', 'middlename')->getIsVisible() && $billingAddress->getMiddlename()) {
                $name .= ' ' . $billingAddress->getMiddlename();
            }
            $name .=  ' ' . $billingAddress->getLastname();
            if ($config->getAttribute('customer', 'suffix')->getIsVisible() && $billingAddress->getSuffix()) {
                $name .= ' ' . $billingAddress->getSuffix();
            }
            return $name;
        }
    }
    
    public function getFiles($commentId){
        return Mage::getModel('amrma/file')->getCollection()
                    ->addFilter("comment_id", $commentId);
    }
    
    public function getSubmitUrl()
    {
        return $this->getUrl('*/*/addComment', array('id' => (int)$this->getRequest()->getParam('id')));
    } 
    
    public function getExportUrl()
    {
        return $this->getUrl('*/*/export', array('id' => (int)$this->getRequest()->getParam('id')));
    }
    
    public function getComfirmUrl()
    {
        return $this->getUrl('*/*/confirm', array('id' => (int)$this->getRequest()->getParam('id')));
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