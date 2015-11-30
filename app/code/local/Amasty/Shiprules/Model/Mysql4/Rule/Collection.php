<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Shiprules
 */ 
class Amasty_Shiprules_Model_Mysql4_Rule_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        $this->_init('amshiprules/rule');
    }
    
    public function addStoreFilter($storeId)
    {
        $storeId = intVal($storeId);
        $this->getSelect()->where('stores="" OR stores LIKE "%,'.$storeId.',%"');
        
        return $this;
    }    
    
    public function addCustomerGroupFilter($groupId)
    {
        $groupId = intVal($groupId);
        $this->getSelect()->where('cust_groups="" OR cust_groups LIKE "%,'.$groupId.',%"');
        
        return $this;
    } 
    
    public function addDaysFilter()
    {
        $this->getSelect()->where('days="" OR days LIKE "%,'.date("N").',%"');
        return $this;
    }         
}