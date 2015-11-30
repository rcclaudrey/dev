<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Rma
 */ 
class Amasty_Rma_Model_Status extends Mage_Core_Model_Abstract
{
    public static $_STATUS_PENDING = 'pending';
    
    public function _construct()
    {
        $this->_init('amrma/status');
    }
    
    public static function getPendingStatus(){
        $status = Mage::getModel('amrma/status')->load(self::$_STATUS_PENDING, 'status_key');
        return $status;
    }
    
    public function getStoreLabels()
    {
        if (!$this->hasStoreLabels()) {
            $labels = $this->_getResource()->getStoreLabels($this->getId());
            $this->setStoreLabels($labels);
        }

        return $this->_getData('store_labels');
    }
    
    public function getStoreLabel($storeId = 0){
        $labels = $this->getStoreLabels();
        return isset($labels[$storeId]) ? $labels[$storeId] : 
            (isset($labels[0]) ? $labels[0] : "");
    }
    
    public function getStoreTemplates()
    {
        if (!$this->hasStoreTemplates()) {
            $templates = $this->_getResource()->getStoreTemplates($this->getId());
            $this->setStoreTemplates($templates);
        }

        return $this->_getData('store_templates');
    }
    
    public function getStoreTemplate($storeId = 0){
        $templates = $this->getStoreTemplates();
        return isset($templates[$storeId]) ? $templates[$storeId] : 
            (isset($templates[0]) ? $templates[0] : "");
    }
}
?>