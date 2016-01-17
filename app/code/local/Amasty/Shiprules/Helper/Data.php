<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Shiprules
 */ 
class Amasty_Shiprules_Helper_Data extends Mage_Core_Helper_Abstract
{
    protected $_counter;
    protected $_firstTime = true;

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
    
    public function getAllCarriers()
    {
        $carriers = array();
        foreach (Mage::getStoreConfig('carriers') as $code=>$config){
            if (!empty($config['title'])){
                $carriers[] = array('value'=>$code, 'label'=>$config['title']);
            }
        }  
        return $carriers;      
    }
    
    public function getStatuses()
    {
        return array(
            '0' => $this->__('Inactive'),
            '1' => $this->__('Active'),
        );       
    }

    public function getCalculations()
    {
        $a = array(
            Amasty_Shiprules_Model_Rule::CALC_REPLACE  => $this->__('Replace'),
            Amasty_Shiprules_Model_Rule::CALC_ADD      => $this->__('Surcharge'),
            Amasty_Shiprules_Model_Rule::CALC_DEDUCT   => $this->__('Discount'),
        );
        return $a;       
    }
    
    public function getAllDays()
    {
        return array(
            array('value'=>'7', 'label' => $this->__('Sunday')),
            array('value'=>'1', 'label' => $this->__('Monday')),
            array('value'=>'2', 'label' => $this->__('Tuesday')),
            array('value'=>'3', 'label' => $this->__('Wednesday')),
            array('value'=>'4', 'label' => $this->__('Thursday')),
            array('value'=>'5', 'label' => $this->__('Friday')),
            array('value'=>'6', 'label' => $this->__('Saturday')),
        );             
    }
    
    public function getAllRules()
    {
        $rules =  array(
            array('value'=>'0', 'label' => $this->__('')));
        
        $rulesCollection = Mage::getResourceModel('salesrule/rule_collection')->load();
        
        foreach ($rulesCollection as $rule){
           $rules[] = array('value'=>$rule->getRuleId(), 'label' => $rule->getName());
        }
        
        return $rules;
    }

    public function getShippingPrice($block, $price, $flag)
    {
        $i = 0;
        $oldPrice = 0;
        $groups = method_exists($block, 'getEstimateRates') ? $block->getEstimateRates() : $block->getShippingRates();
        foreach ($groups as $group) {
            foreach ($group as $rate) {
                $oldPrice = $rate->getOldPrice();
                if ($i == $block->_counter) {
                    break 2;
                }
                $i++;
            }
        }
        $newPrice = $block->getQuote()->getStore()->convertPrice(
                Mage::helper('tax')->getShippingPrice($price, $flag, $block->getAddress()), $block->getQuote()->getCustomerTaxClassId()
            );

        if ($block->_firstTime) {
            $block->_counter++;
            $block->_firstTime = false;
        } else {
            $block->_firstTime = true;
        }
        if (Mage::getStoreConfig("amshiprules/discount/show_discount") && ($oldPrice > $price)) {
            $newPrice = '<span style="' . Mage::getStoreConfig("amshiprules/discount/old_price_style") . '">' . $oldPrice . '</span>' . ' ' .
                '<span style="' . Mage::getStoreConfig("amshiprules/discount/new_price_style") . '">' . $newPrice . '</span>';
        }
        return $newPrice;
    }
         
}