<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Acart
 */
class Amasty_Acart_Model_Observer 
{
    function onSalesOrderPlaceAfter($observer){
        
        $order = $observer->getOrder();
        $quote = $order->getQuote();
        if ($quote){
//          
            Mage::getModel('amacart/schedule')->buyQuote($quote);
        }
    }
    
    function clearCoupons(){
        $allCouponsCollection = Mage::getModel('salesrule/rule')->getCollection();
        
        $allCouponsCollection->join(

            array('history' => 'amacart/history'),
            'main_table.rule_id = history.sales_rule_id', 
            array('history.history_id')
        );
        
        $allCouponsCollection->getSelect()->where(
            'main_table.to_date < "'.date('Y-m-d', time()).'"'
        );
        
        foreach ($allCouponsCollection->getItems() as $aCoupon) {
            $aCoupon->delete();
        }
    }
    
    function refreshHistory(){
        Mage::getModel('amacart/schedule')->run();
    }
    
    /**
     * Append rule product attributes to select by quote item collection
     *
     * @param Varien_Event_Observer $observer
     * @return Mage_SalesRule_Model_Observer
     */
    public function addProductAttributes(Varien_Event_Observer $observer)
    {
        // @var Varien_Object
        $attributesTransfer = $observer->getEvent()->getAttributes();

        $attributes = Mage::getResourceModel('amacart/rule')->getAttributes();
        
        $result = array();
        foreach ($attributes as $code) {
            $result[$code] = true;
        }
        
        $attributesTransfer->addData($result);
        
        return $this;
    } 
    
    public function onSalesruleValidatorProcess($observer)
    {
        $ret = true;
        $ruleId = $observer->getEvent()->getRule()->getRuleId();

        $history = null;

        foreach(Mage::getModel("amacart/history")->getCollection()
                    ->addFieldToFilter("sales_rule_id", $ruleId) as $item){
            if ($item->getCouponCode() == $observer->getEvent()->getRule()->getCode()){
                $history = $item;
                break;
            }
        }

        if ($history && $history->getId()){
            $customerEmail = $history->getCustomerId() ?
                    $observer->getEvent()->getQuote()->getCustomer()->getEmail() :
                    $observer->getEvent()->getQuote()->getBillingAddress()->getEmail()
                ;
            $customerCoupon = Mage::getStoreConfig("amacart/general/customer_coupon");
            
            if ($customerCoupon && $customerEmail != $history->getEmail()) {
                $observer->getEvent()->getQuote()->setCouponCode("");
            }
        }


        return $ret;
        
    }
}
?>