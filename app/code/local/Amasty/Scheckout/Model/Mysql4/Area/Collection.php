<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Scheckout
 */
class Amasty_Scheckout_Model_Mysql4_Area_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        $this->_init('amscheckout/area');
    }
    
    function byStore($storeId){
        $adapter = $this->getConnection();

        
        $areaStoreTable = Mage::getSingleton('core/resource')
            ->getTableName('amscheckout/area_store');
        
        $storeCondition = $adapter->quoteInto('a_s.store_id=?', $storeId);
        
        $this->getSelect()->joinLeft(array(
            'a_s' => $areaStoreTable
        ), 'main_table.area_id = a_s.area_id and ' . $storeCondition, 
        array(
            'a_s.area_store_id', 'a_s.area_label as st_area_label', 
        ));
        return $this;
    }
    
}