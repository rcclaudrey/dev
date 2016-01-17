<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Xsearch
 */  
class Amasty_Xsearch_Model_Observer {
    
    public function onCatalogsearchIndexProcessStart($observer) {
        $event = $observer->getEvent();
        
        if (is_null($event->getStoreId()) && is_null($event->getProductIds())) {
            
        }
        return $this;
    }
    
    public function onControllerActionPredispatchAdminhtmlProcessReindexProcess($observer){
        $action = $observer->getControllerAction();
        
        $processId = $action->getRequest()->getParam('process');
        if ($processId) {
            $process = Mage::getModel('index/process')->load($processId);
            if ($process->getId() && $process->getIndexerCode() === 'catalogsearch_fulltext') {
               
            }
        }
    }
        
    public function onAdminSystemConfigSectionSaveAfter($observer){
        if ($observer->getSection() == 'amxsearch'){
       
            Mage::getSingleton('index/indexer')
                ->getProcessByCode('catalogsearch_fulltext')
                ->changeStatus(Mage_Index_Model_Process::STATUS_REQUIRE_REINDEX);
    }

}
}
?>