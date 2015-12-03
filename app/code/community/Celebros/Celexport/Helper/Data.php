<?php
/**
 * Celebros Qwiser - Magento Extension
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish correct extension functionality.
 * If you wish to customize it, please contact Celebros.
 *
 * @category    Celebros
 * @package     Celebros_Celexport
 */
class Celebros_Celexport_Helper_Data extends Mage_Core_Helper_Abstract
{
    
    public function getCelebrosConfigData($field, $storeId = null)
    {
        $path = 'celexport/' . $field;
        return Mage::getStoreConfig($path, $storeId);
    }
    
    public function setStoreExportStatus($store_export_status)
    {
        Mage::unregister("celexport_store_export_status");
        Mage::register("celexport_store_export_status", $store_export_status);
    }
    
    public function getStoreExportStatus()
    {
        return Mage::registry("celexport_store_export_status");
    }
    
    public function setCronJobCode($cron_execution_time)
    {
        Mage::unregister("celexport_cron_execution_time");
        Mage::register("celexport_cron_execution_time", $cron_execution_time);
    }
    
    public function getCronJobCode()
    {
        return Mage::registry("celexport_cron_execution_time");
    }
    
    
    public function getUrlParam($param)
    {
        return strip_tags(Mage::app()->getRequest()->getParam($param));
    }
    
    public function sanitizeOutput($text)
    {
        return htmlentities($text, ENT_QUOTES, 'UTF-8');
    } 
    
    public function getExportChunkSize()
    {
        $chunk_size = $this->getCelebrosConfigData('advanced/export_chunk_size');
        if ($chunk_size == '') {
            $chunk_size = 1000;
        }
        return $chunk_size;
    }
    
    public function getExportProcessLimit()
    {
        $limit = $this->getCelebrosConfigData('advanced/export_process_limit');
        if ($limit == '') {
            $limit = 3;
        }
        return $limit;
    }
    
    public function getExportPath($id)
    {
        return Mage::getBaseDir() . Mage::app()->getStore(0)->getConfig('celexport/export_settings/path') . DS . $id;
    }
    
    public function getExportProcessId()
    {
        $date = new DateTime();
        return $date->getTimestamp();
    }
    
}