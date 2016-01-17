<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Acart
 */ 
class Amasty_Acart_Model_History extends Mage_Core_Model_Abstract
{
    const STATUS_PENDING = 'pending';
    const STATUS_SENT = 'sent';
    const STATUS_PROCESSING = 'processing';
    const STATUS_BLACKLIST = 'blacklist';
    const STATUS_DONE = 'done';
    
    public function _construct()
    {
        parent::_construct();
        $this->_init('amacart/history');
    }
    
    public function getPlaceOrderUrl(){
        return Mage::getUrl('amacartfront/main/order', array(
            'id' => $this->getId(),
            'key' => $this->getPublicKey(),
        ));
    }


    public function getUnsubscribeUrl(){

        return Mage::getUrl('amacartfront/main/unsubscribe', array(
            'id' => $this->getId(),
            'key' => $this->getPublicKey(),
        ));
    }
    
    public function getCheckoutUrl(){
        return $this->createCustomUrl(Mage::getUrl('checkout/index', array('_secure' => true)));
    }
    
    public function createCustomUrl($target){
        return Mage::getUrl('amacartfront/main/custom', array(
            'id' => $this->getId(),
            'target' => base64_encode($target),
            'key' => $this->getPublicKey(),
        ));
    }
    
    
    function getRule($schedule){
        $rule = null;

        if ($schedule->getUseRule()){
            $rule = Mage::getModel('salesrule/rule')->load($schedule->getSalesRuleId());
        } else if ($schedule->getCouponType() !== NULL){
            $store = Mage::app()->getStore($this->getStoreId()); 

            $rule = $this->_createCoupon(
                    $store, 
                    $schedule
            );
        }
        
        return $rule;
        
    }
    
    protected function _getCouponToDate($days, $delayedStart){
        return date('Y-m-d', (time() + $days*24*3600 + $delayedStart) );
    }
    
    function _createCoupon($store, $schedule)
    {
        $rule = NULL;
        
      	$couponData = array();
        $couponData['name']      = 'Alert #' . $this->getId();
        $couponData['is_active'] = 1;
        $couponData['website_ids'] = array(0 => $store->getWebsiteId());
        $couponData['coupon_code'] = strtoupper(uniqid()); // todo check for uniq in DB
        $couponData['uses_per_coupon'] = 1;
        $couponData['uses_per_customer'] = 1;
        $couponData['from_date'] = ''; //current date

//        $days = Mage::getStoreConfig('catalog/adjcartalert/coupon_days', $store);
//        $date = Mage::helper('core')->formatDate(date('Y-m-d', time() + $days*24*3600));
        $couponData['to_date'] = $this->_getCouponToDate($schedule->getExpiredInDays(), $schedule->getDelayedStart());
        
        $couponData['uses_per_customer'] = 1;
        $couponData['coupon_type'] = 2;
        
        $couponData['simple_action']   = $schedule->getCouponType();//Mage::getStoreConfig('catalog/adjcartalert/coupon_type', $store);
        $couponData['discount_amount'] = $schedule->getDiscountAmount();//Mage::getStoreConfig('catalog/adjcartalert/coupon_amount', $store);
        $couponData['stop_rules_processing'] = 0;

        if ($schedule->getDiscountQty())
            $couponData['discount_qty'] = $schedule->getDiscountQty();
        
        if ($schedule->getDiscountStep())
            $couponData['discount_step'] = $schedule->getDiscountStep();
        
        if ($schedule->getPromoSku())
            $couponData['promo_sku'] = $schedule->getPromoSku();  
        
        $couponData['conditions'] = array(
            '1' => array(
                'type'       => 'salesrule/rule_condition_combine',
                'aggregator' => 'all',
                'value'      => 1,
                'new_child'  =>'', 
            )
        );
        
        if ($schedule->getSubtotalGreaterThan()){
            $couponData['conditions']['1--1'] = array(
               'type'      => 'salesrule/rule_condition_address',
               'attribute' => 'base_subtotal',
               'operator'  => '>=',
               'value'     => $schedule->getSubtotalGreaterThan()
           );
        }
        
        $couponData['actions'] = array(
            1 => array(
                'type'       => 'salesrule/rule_condition_product_combine',
                'aggregator' => 'all',
                'value'      => 1,
                'new_child'  =>'', 
            )
        );
        
        //create for all customer groups
        $couponData['customer_group_ids'] = array();
        
        $customerGroups = Mage::getResourceModel('customer/group_collection')
            ->load();

        $found = false;
        foreach ($customerGroups as $group) {
            if (0 == $group->getId()) {
                $found = true;
            }
            $couponData['customer_group_ids'][] = $group->getId();
        }
        if (!$found) {
            $couponData['customer_group_ids'][] = 0;
        }
        
        try { 
            $rule = Mage::getModel('salesrule/rule')
                ->loadPost($couponData)
                ->save();      
        } 
        catch (Exception $e){
            //print_r($e); exit;
            $couponData['coupon_code'] = '';   
        }
        
        return $rule;
    }
    
    public function getRecoveredNumberByEmail($email, $dateInterval='month'){
        $historyCollection = Mage::getModel('amacart/history')->getCollection();
        $historyCollection->addFieldToSelect('canceled_id');
        
        $historyCollection->addFilter('email', $email);
        $historyCollection->getSelect()->where('canceled_id is not null');
        
        
        switch($dateInterval){
            case "month":
                $historyCollection->addFieldToFilter('main_table.created_at', 
                    array('gteq' => date('Y-m-01', time())
                ));

                $historyCollection->addFieldToFilter('main_table.created_at', 
                    array('lteq' => date('Y-m-t', time())
                ));
                break;
        }
        
        $ids = array();
        foreach($historyCollection as $history){
            $ids[$history->getCanceledId()] = $history->getCanceledId();
        }
        
        $canceledCollection = Mage::getModel('amacart/canceled')->getCollection();
        $canceledCollection->addFieldToFilter('canceled_id', array('in' => $ids));
        
        $canceledCollection->addFieldToFilter('reason', 
                array('in' => array(
                    Amasty_Acart_Model_Canceled::REASON_BOUGHT,
                    Amasty_Acart_Model_Canceled::REASON_LINK
                ))
        );
        
        $num = $canceledCollection->getSize();
        
        return $num;
    }
}
?>