<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Scheckout
 */
class Amasty_Scheckout_Model_Mysql4_Field_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        $this->_init('amscheckout/field');
    }
    
    function byStore($storeId){
        $adapter = $this->getConnection();

        
        $fieldStoreTable = Mage::getSingleton('core/resource')
            ->getTableName('amscheckout/field_store');
        
        $storeCondition = $adapter->quoteInto('f_s.store_id=?', $storeId);
        
        $this->getSelect()->joinLeft(array(
            'f_s' => $fieldStoreTable
        ), 'main_table.field_id = f_s.field_id and ' . $storeCondition, 
        array(
            'f_s.field_store_id', 
            'f_s.field_label as st_field_label', 'f_s.field_order as st_field_order', 
            'f_s.field_required as st_field_required', 'f_s.column_position as st_column_position',
            'f_s.field_disabled as st_field_disabled'
        ));

        return $this;
    }
    
    function filterByArea($key = NULL){
        if ($key){
            $this->getSelect()->joinLeft(array(
                'a' => $this->getTable('amscheckout/area')
            ), 'main_table.area_id = a.area_id', 
            array());
            $this->addFilter("a.area_key", $key);
        }
    }
    
}