<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Acart
 */
class Amasty_Acart_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getAllGroups()
    {
        $customerGroups = Mage::getResourceModel('customer/group_collection')
            ->load()->toOptionArray();

        $found = false;
        foreach ($customerGroups as $group) {
            if ($group['value']==0) {
                $found = true;
            }
        }
        if (!$found) {
            array_unshift($customerGroups, array('value'=>0, 'label'=>Mage::helper('salesrule')->__('NOT LOGGED IN')));
        } 
        
        return $customerGroups;
    }
        
    public function getStatuses()
    {
        return array(
                '1' => Mage::helper('salesrule')->__('Active'),
                '0' => Mage::helper('salesrule')->__('Inactive'),
            );       
    }
    
    public function getCancelRules(){
        return array(
            Amasty_Acart_Model_Rule::CANCEL_RULE_BOUGHT => Mage::helper('amacart')->__('Order Placed'),
            Amasty_Acart_Model_Rule::CANCEL_RULE_LINK => Mage::helper('amacart')->__('Link from Email Clicked'),
        ); 
    }
    
    public function getCouponTypes(){
        return array(
            '' => Mage::helper('amacart')->__('-- None --'),
            Amasty_Acart_Model_Rule::COUPON_CODE_BY_PERCENT => Mage::helper('amacart')->__('Percent of product price discount'),
            Amasty_Acart_Model_Rule::COUPON_CODE_BY_FIXED => Mage::helper('amacart')->__('Fixed amount discount'),
            Amasty_Acart_Model_Rule::COUPON_CODE_CART_FIXED => Mage::helper('amacart')->__('Fixed amount discount for whole cart'),
        ); 
    }
    
    public function getReasonsTypes(){
        return array(
            '' => Mage::helper('amacart')->__('-- None --'),
            Amasty_Acart_Model_Canceled::REASON_ELAPSED => Mage::helper('amacart')->__('Sent'),
            Amasty_Acart_Model_Canceled::REASON_BOUGHT => Mage::helper('amacart')->__('Recovered'),
            Amasty_Acart_Model_Canceled::REASON_LINK => Mage::helper('amacart')->__('Link Opened'),
            Amasty_Acart_Model_Canceled::REASON_BALCKLIST => Mage::helper('amacart')->__('Added to Black List'),
            Amasty_Acart_Model_Canceled::REASON_ADMIN => Mage::helper('amacart')->__('Cancelled by Admin'),
            Amasty_Acart_Model_Canceled::REASON_UPDATED => Mage::helper('amacart')->__('New Cart Created'),
        ); 
    }
}