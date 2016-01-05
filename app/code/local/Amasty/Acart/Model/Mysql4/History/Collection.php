<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Acart
 */

/**
 * @author Amasty
 */ 
class Amasty_Acart_Model_Mysql4_History_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        $this->_init('amacart/history');
    }
      
    function addQuoteData(){
        $this->getSelect()->join( 
                array('schedule' => $this->getTable('amacart/schedule')), 
                'main_table.schedule_id = schedule.schedule_id',
                array('schedule.*')
        );
        
        $this->getSelect()->join( 
                array('quote' => $this->getTable('sales/quote')), 
                'main_table.quote_id = quote.entity_id',
                array('quote.*')
        );
    }
    
    function addCanceledData(){
        $this->getSelect()->joinLeft(
            array('canceled' => $this->getTable('amacart/canceled')), 
            'main_table.canceled_id = canceled.canceled_id', 
            array('canceled.canceled_id')
        );
        
        $this->addFieldToFilter('canceled.canceled_id', array('null' => true));
    }
    
    function addBlacklistData(){
        $this->getSelect()->join(
            array('blacklist' => $this->getTable('amacart/blacklist')), 
            'main_table.email = blacklist.email', 
            array('blacklist.blacklist_id')
        );
    }
      
}