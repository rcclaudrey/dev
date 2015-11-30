<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Shiprules
 */ 
class Amasty_Shiprules_Model_Rule extends Mage_Rule_Model_Rule
{
    const CALC_REPLACE = 0;
    const CALC_ADD     = 1;
    const CALC_DEDUCT  = 2;
     
    public function _construct()
    {
        parent::_construct();
        $this->_init('amshiprules/rule');
    }
    
    public function getConditionsInstance()
    {
        return Mage::getModel('amshiprules/rule_condition_combine');
    }
    
    public function getActionsInstance()
    {
        return Mage::getModel('salesrule/rule_condition_product_combine');
    }    
    
    public function massChangeStatus($ids, $status)
    {
        return $this->getResource()->massChangeStatus($ids, $status);
    }
    
    /**
     * Initialize rule model data from array
     *
     * @param   array $rule
     * @return  Mage_SalesRule_Model_Rule
     */
    public function loadPost(array $rule)
    {
        $arr = $this->_convertFlatToRecursive($rule);
        if (isset($arr['conditions'])) {
            $this->getConditions()->setConditions(array())->loadArray($arr['conditions'][1]);
        }
        if (isset($arr['actions'])) {
            $this->getActions()->setActions(array())->loadArray($arr['actions'][1], 'actions');
        }
        return $this;
    }  
    
    public function match($rate)
    {
        if (false === strpos($this->getCarriers(), ',' . $rate->getCarrier(). ',')){
            return false;
        }
        
        $m = $this->getMethods();    
        $m = str_replace("\r\n", "\n", $m);
        $m = str_replace("\r", "\n", $m);
        $m = trim($m);
        if (!$m){ // any method
            return true;
        }
        
        $m = array_unique(explode("\n", $m));
        foreach ($m as $pattern){
            $pattern = '/' . trim($pattern) . '/i';
            if (preg_match($pattern, $rate->getMethodTitle())){
                return true;
            }
        }
        return false;
    }
    
    public function validateTotals($totals)
    {
        $keys = array('price', 'qty', 'weight');
        foreach ($keys as $k){
            $v = $this->getIgnorePromo() ? $totals[$k] : $totals['not_free_' . $k];
            if ($this->getData($k . '_from') > 0 && $v < $this->getData($k . '_from')){
                return false;
            }
            
            if ($this->getData($k . '_to')   > 0 && $v > $this->getData($k . '_to')){
                return false;
            }
        }
        
        return true;     
    }
    //chnages inner variable fee
    public function calculateFee($totals, $isFree)
    {
        if ($isFree && !$this->getIgnorePromo()){
            $this->setFee(0);
            return 0;     
        }

        $rate = 0; 
        
        // fixed per each item
        $qty = $this->getIgnorePromo() ? $totals['qty'] : $totals['not_free_qty'];
        $weight = $this->getIgnorePromo() ? $totals['weight'] : $totals['not_free_weight'];
        if ($qty > 0){
            // base rate, but only in cases at lest one product is not free
            $rate += $this->getRateBase();
        }

        $rate += $qty * $this->getRateFixed();
        
        // percent per each item
        $price = $this->getIgnorePromo() ? $totals['price'] : $totals['not_free_price'];
        $rate += $price * $this->getRatePercent() / 100;
        $rate += $weight * $this->getWeightFixed();

        if ($this->getCalc() == self::CALC_DEDUCT){
            $rate = 0 - $rate; // negative    
        }
        
        $this->setFee($rate);
        
        return $rate;     
    }  
    
    public function removeFromRequest()
    {
        return ($this->getCalc() == self::CALC_REPLACE);
    } 
    
    
    protected function _afterSave()
    {
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