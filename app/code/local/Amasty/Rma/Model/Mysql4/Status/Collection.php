<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Rma
 */ 
class Amasty_Rma_Model_Mysql4_Status_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        $this->_init('amrma/status');
    }
    
    public function sortByOrder($direction = self::SORT_ORDER_ASC){
        $this->getSelect()->order("ifnull(order_number, 9999)", $direction);
        $this->getSelect()->order("status_id", 'asc');
        return $this;
    }
    
    public function addLabel($storeId = 0){
        $this->getSelect()
            ->joinLeft(
                array('label' => $this->getTable('amrma/label')), 
                'main_table.status_id = label.status_id and label.store_id = '.$storeId, 
                array('label.label')
            );
        return $this;
    }
    
}