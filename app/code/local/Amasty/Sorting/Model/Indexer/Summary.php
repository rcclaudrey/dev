<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Sorting
 */
class Amasty_Sorting_Model_Indexer_Summary extends Mage_Index_Model_Indexer_Abstract
{
    /**
     * Retrieve Indexer name
     *
     * @return string
     */
    public function getName()
    {
        return Mage::helper('amsorting')->__('Improved Sorting');
    }

    /**
     * Retrieve Indexer description
     *
     * @return string
     */
    public function getDescription()
    {
        return Mage::helper('amsorting')->__('Data for best-selling, most viewed and wishlist sorting options');
    }    

    /**
     * Register data required by process in event object
     *
     * @param Mage_Index_Model_Event $event
     */
    protected function _registerEvent(Mage_Index_Model_Event $event)
    {
    		
    }
    
    public function matchEvent(Mage_Index_Model_Event $event)
    {
        return false;
    }     

    /**
     * Process event
     *
     * @param Mage_Index_Model_Event $event
     */
    protected function _processEvent(Mage_Index_Model_Event $event)
    {

    }
    
    
    public function reindexAll()
    {
        // for cron job on 1.3
        if (version_compare(Mage::getVersion(), '1.4') < 0)
            return;   
            
        // for cron job on 1.3
        if (!Mage::getStoreConfig('amsorting/general/use_index'))
            return;                  
        
        $methods = Mage::helper('amsorting')->getMethods();
        foreach ($methods as $code){
            $method = Mage::getSingleton('amsorting/method_' . $code);
            $method->reindex();
        }
        
        // we need it for cron job
        $indexer = Mage::getSingleton('index/indexer');
        $process = $indexer->getProcessByCode('amsorting_summary');
        if ($process) {
            $process->setStatus(Mage_Index_Model_Process::STATUS_PENDING);
            $process->setEndedAt(date('Y-m-d H:i:s'));
            $process->save();
        }            
    }    
}