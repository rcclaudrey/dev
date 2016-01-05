<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Acart
 */ 
class Amasty_Acart_Model_Rule extends Mage_Rule_Model_Rule
{
    const CANCEL_RULE_BOUGHT = 'bought';
    const CANCEL_RULE_LINK = 'link';
    
    const COUPON_CODE_BY_PERCENT = 'by_percent';
    const COUPON_CODE_BY_FIXED = 'by_fixed';
    const COUPON_CODE_CART_FIXED = 'cart_fixed';
    
    public function _construct()
    {
        parent::_construct();
        $this->_init('amacart/rule');
    }
    
    public function getConditionsInstance()
    {
        return Mage::getModel('amacart/rule_condition_combine');
    }
    
    protected function _toSeconds($days, $hours, $minutes){
        return $minutes * 60 + ($hours * 60 * 60) + ($days * 24 * 60 * 60);
    }
    
    protected function _afterSave()
    {
        if (is_array($this->getSchedule())){
            
            $scheduleCollection = Mage::getModel('amacart/schedule')->getCollection();
            $scheduleCollection->addFilter('rule_id', $this->getId());
            
            $scheduleDbData = $scheduleCollection->getItems();
            
            $schedule = $this->getSchedule();
            $saveData = array();
            
            foreach($schedule['row_index'] as $order){
                
                if ($order != '_rowIdx_') { //skip first template row

                    $days = intval($schedule['days'][$order]);
                    $hours = intval($schedule['hours'][$order]);
                    $minutes = intval($schedule['minutes'][$order]);

                    $rowIndex = intval($schedule['row_index'][$order]);
                    $email_template_id = $schedule['email_templates'][$order];

                    $delayed_start = $this->_toSeconds($days, $hours, $minutes);
                    $saveItem = array();
                    if (intval($schedule['use_rule'][$order]) == 1) {
                        $saveItem = array(
                            "use_rule" => 1,
                            "sales_rule_id" => $schedule['rule_id'][$order],
                            'delayed_start' => $delayed_start,
                            'email_template_id' => empty($email_template_id) ? NULL : $email_template_id,
                        );
                    } else {
                        $coupon_type = $schedule['coupon_type'][$order];
                        $discount_amount = intval($schedule['discount_amount'][$order]);

                        $expired_in_days = intval($schedule['expired_in_days'][$order]);
                        $discount_qty = $schedule['discount_qty'][$order];
                        $discount_step = $schedule['discount_step'][$order];
                        $promo_sku = $schedule['promo_sku'][$order];
                        $ampromo_type = $schedule['ampromo_type'][$order];
                        $subtotal_greater_than = $schedule['subtotal_greater_than'][$order];

                        $saveItem = array(
                            'email_template_id' => empty($email_template_id) ? NULL : $email_template_id,
                            'delayed_start' => $delayed_start,
                            'coupon_type' => empty($coupon_type) ? NULL : $coupon_type,
                            'discount_amount' => $discount_amount,
                            'expired_in_days' => $expired_in_days,
                            'discount_qty' => $discount_qty,
                            'discount_step' => $discount_step,
                            'promo_sku' => $promo_sku,
                            'ampromo_type' => $ampromo_type,
                            'use_rule' => 0,
                            'subtotal_greater_than' => empty($subtotal_greater_than) ? NULL : $subtotal_greater_than,
                        );
                    }

                    $saveData[$order] = $saveItem;
                }
            }
            
            foreach($scheduleDbData as $scheduleDbItem){
                $delayed_start = $scheduleDbItem->getDelayedStart();
                
                if (array_key_exists($delayed_start, $saveData)){
                    
//                    $scheduleDbItem->setEmailTemplateId($saveData[$delayed_start]);
                    $scheduleDbItem->addData($saveData[$delayed_start]);
                    
                    $scheduleDbItem->save();
                    unset($saveData[$delayed_start]);
                } else {
                    $scheduleDbItem->delete();
                }
                
            }
            
            foreach($saveData as $delayed_start => $config){
                
                $schedule = Mage::getModel('amacart/schedule');
                $schedule->setData(array_merge(array(
                    'rule_id' => $this->getId(),
                    'email_template_id' => $email_template_id
                ), $config));
                
                $schedule->save();
            }
        }
        
        //Saving attributes used in rule
        $ruleProductAttributes = array_merge(
            $this->_getUsedAttributes($this->getConditionsSerialized()),
            $this->_getUsedAttributes($this->getActionsSerialized())
        );
        if (count($ruleProductAttributes)) {
            $this->getResource()->saveAttributes($this->getId(), $ruleProductAttributes);
        } 
        
        return parent::_afterSave(); 
    } 
    
    /**
     * Return all product attributes used on serialized action or condition
     *
     * @param string $serializedString
     * @return array
     */
    protected function _getUsedAttributes($serializedString)
    {
        $result = array();
        
        $pattern = '~s:32:"salesrule/rule_condition_product";s:9:"attribute";s:\d+:"(.*?)"~s';
        $matches = array();
        if (preg_match_all($pattern, $serializedString, $matches)){
            foreach ($matches[1] as $attributeCode) {
                $result[] = $attributeCode;
            }
        }
        
        return $result;
    }  
    
    public function validate(Varien_Object $quote){
        $storesIds = $this->getStores();
        
        $arrStores = explode(',', $storesIds);
                
        $storesValidation = empty($storesIds) || in_array($quote->getStoreId(), $arrStores);
        
        $customerGroupsIds = $this->getCustGroups();
        $arrCustomerGroups = explode(',', $customerGroupsIds);
        
        $customerGroupValidation = empty($customerGroupsIds) || in_array($quote->getCustomerGroupId(), $arrCustomerGroups);
        
        return $storesValidation && $customerGroupValidation && $this->_validateAddress($quote);
    }
    
    protected function _validateAddress($quote){
        $ret = false;
        
        foreach($quote->getAllAddresses() as $address){
            $this->_initAddress($address, $quote);
        
            if (parent::validate($address)){
                $ret = true;
                break;
    }
        }
        return $ret;
    }
    
    protected function _initAddress($address, $quote){
    
        $address->setData('total_qty', $quote->getData('items_qty'));
        return $address;
    }
    
    protected function _setWebsiteIds(){
            $websites = array();

            foreach (Mage::app()->getWebsites() as $website) {
                foreach ($website->getGroups() as $group) {
                    $stores = $group->getStores();
                    foreach ($stores as $store) {
                        $websites[$website->getId()] = $website->getId();
                    }
                }
            }

            $this->setOrigData('website_ids', $websites);
        }

        protected function _beforeSave(){
            $this->_setWebsiteIds();
            return parent::_beforeSave();
        }

        protected function _beforeDelete(){
            $this->_setWebsiteIds();
            return parent::_beforeDelete();
        }
    
    
}