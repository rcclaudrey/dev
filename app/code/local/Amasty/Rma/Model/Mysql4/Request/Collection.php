<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Rma
 */ 
class Amasty_Rma_Model_Mysql4_Request_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        $this->_init('amrma/request');
    }
    
    public function addStatusLabel($storeId = 0){
        $this->getSelect()
            ->joinLeft(
                array('label' => $this->getTable('amrma/label')), 
                'main_table.status_id = label.status_id and label.store_id = '.$storeId, 
                array('label.label')
            );
        return $this;
    }
    
    public function addStoreFilter($store, $withAdmin = true){

        if ($store instanceof Mage_Core_Model_Store) {
            $store = array($store->getId());
        }

        if (!is_array($store)) {
            $store = array($store);
        }

        $this->addFilter('main_table.store_id', array('in' => $store));

        return $this;
    }
      
}