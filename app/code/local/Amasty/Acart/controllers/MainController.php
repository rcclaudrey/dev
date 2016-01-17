<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Acart
 */
class Amasty_Acart_MainController extends Mage_Core_Controller_Front_Action
{
    
    protected function _getHistory(){
        $ret = NULL;
        $historyId = $this->getRequest()->id;
        $key = $this->getRequest()->key;
        
        $history = Mage::getModel('amacart/history')->load($historyId);
        
        if ($history->getId() && $history->getPublicKey() == $key){
            $ret = $history;
        }
        
        return $ret;
    }
    
    protected function _loginCustomer($history){
        $s = Mage::getSingleton('customer/session');
        if ($s->isLoggedIn()){
            if ($history->getCustomerId() != $s->getCustomerId()){
                $s->logout();
            }                   
        }
        // customer. login
        if ($history->getCustomerId()){
            $customer = Mage::getModel('customer/customer')->load($history->getCustomerId());
            if ($customer->getId())
                $s->setCustomerAsLoggedIn($customer);
        }
        elseif ($history->getQuoteId()){
            //visitor. restore quote in the session
            $quote = Mage::getModel('sales/quote')->load($history->getQuoteId());
            if ($quote){
                Mage::getSingleton('checkout/session')->replaceQuote($quote); 
                $quote->getBillingAddress()->setEmail($history->getEmail());
            }
        }
        
        if ($history->getSalesRuleId()){
            $salesRule = Mage::getModel('salesrule/rule')->load($history->getSalesRuleId());
            $code = $salesRule->getCouponCode();
            $quote = Mage::getSingleton('checkout/session')->getQuote();
            if ($code && $quote){

                $quote->setCouponCode($code)
                    ->collectTotals()
                    ->save();
            }
        }
        
    }
    
    public function customAction(){
        $history = $this->_getHistory();
        
        $target = $this->getRequest()->target;
        
        if ($history && $target){
            $target = base64_decode($target);
            
            $this->_loginCustomer($history);
            
            $schedule = Mage::getModel('amacart/schedule')->load($history->getScheduleId());
            $schedule->clickByLink($history);
            Mage::app()->getFrontController()->getResponse()->setRedirect($target);
        } else {
            $this->_customRedirect("/");
        }
        
        
    }
            
    public function orderAction()
    {
        $history = $this->_getHistory();
        
        if ($history){
            
            $this->_loginCustomer($history);

            $schedule = Mage::getModel('amacart/schedule')->load($history->getScheduleId());
            $schedule->clickByLink($history);
        }
        
        $this->_customRedirect('checkout/cart');
    }
    
    protected function _customRedirect($path, $args = array()){
        $url = Mage::getUrl($path, $args);
        
        if (isset($_SERVER['QUERY_STRING']))
            $url .= "?".$_SERVER['QUERY_STRING'];
        
        $this->_redirectUrl($url);
    }
    
    public function unsubscribeAction()
    {
        $history = $this->_getHistory();
        if ($history){
            $schedule = Mage::getModel('amacart/schedule')->load($history->getScheduleId());
            $schedule->unsubscribe($history);
            
            Mage::getSingleton('catalog/session')->addSuccess(Mage::helper('amacart')->__('You have been unsubscribed'));
        }
        
        $this->_customRedirect('checkout/cart');
    }
    
    public function emailAction()
    {
        $value = $this->getRequest()->value;
        
        $quote = Mage::getModel('checkout/cart')->getQuote();
        if ($quote->getId()){
            $quote2email = Mage::getModel('amacart/quote2email')->load($quote->getId(), 'quote_id');
            
            $quote2email->setData(array(
                'quote2email_id' => $quote2email->getId(),
                'quote_id' => $quote->getId(),
                'email' => $value
            ));
            
            $quote2email->save();
        }
    }

    public function urlAction(){

        $history = $this->_getHistory();

        $target = $this->getRequest()->target;

        if ($history && $target){

            $target = base64_decode($target);

            $this->_loginCustomer($history);
            $params = $this->getRequest()->getParams();
            unset($params['target']);

            foreach($params as $key => $val){
                $target .= (strpos($target, "?") !== FALSE ? "&" : "?") . $key . '='.$val;
            }

            Mage::app()->getFrontController()->getResponse()->setRedirect($target);
        } else {
            $this->_customRedirect("/");
        }
    }
       
}

?>