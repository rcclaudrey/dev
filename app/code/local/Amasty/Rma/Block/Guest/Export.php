<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Rma
 */ 
class Amasty_Rma_Block_Guest_Export extends Mage_Core_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        
        $collection = Mage::getResourceModel('amrma/item_collection')
            ->addFilter('request_id', $this->getRmaRequest()->getId())
        ;

        $this->setItems($collection);   
    }
    
    protected function _prepareLayout()
    {
        $this->getLayout()->getBlock('head')->setTitle(Mage::helper('amrma')->__('Export'));
        return parent::_prepareLayout();
    }
    
    public function getLogoSrc()
    {
        if (empty($this->_data['logo_src'])) {
            $this->_data['logo_src'] = Mage::getStoreConfig('design/header/logo_src');
        }
        return $this->getSkinUrl($this->_data['logo_src']);
    }
    
    public function getLogoAlt()
    {
        if (empty($this->_data['logo_alt'])) {
            $this->_data['logo_alt'] = Mage::getStoreConfig('design/header/logo_alt');
        }
        return $this->_data['logo_alt'];
    }
    
    public function getRmaRequest(){
        return Mage::registry('amrma_request');
    }
    
    public function getReturnAddress(){
        $hlr = Mage::helper("amrma");
        return $hlr->getReturnAddress();
    }
    
    public function getCustomerAddress(){
        $request = $this->getRmaRequest();
        $saleOrder = Mage::getModel('sales/order')->load($request->getOrderId());
        return $saleOrder->getShippingAddress()->format('html');
    }
    
    public function getDate($d){
        $format = Mage::app()->getLocale()->getDateFormat(
            Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM
        );
        
        return Mage::app()->getLocale()
                    ->date($d, Varien_Date::DATETIME_INTERNAL_FORMAT)->toString($format);
    }
}
?>