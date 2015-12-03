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
require_once $argv[3] . '/abstract.php';

class Celebros_Shell_ProcessChunkCol extends Mage_Shell_abstract
{
    protected $_storeId = NULL;
    protected $_processId = NULL;
    
    public function __construct() {
        parent::__construct();
        $this->_storeId = (int)$_SERVER['argv'][2];
        $this->_processId = (int)$_SERVER['argv'][1];
    }
    
    public function getStoreId()
    {
        if (!$this->_storeId) {
            $this->_storeId = (int)$_SERVER['argv'][2];
        }
        
        return $this->_storeId;
    }
    
    public function getProcessId()
    {
        if (!$this->_processId) {
            $this->_processId = (int)$_SERVER['argv'][1];
        }
        
        return $this->_processId;
    }
    
    public function run()
    {
        $exportHelper = Mage::helper('celexport/export');
        ini_set('memory_limit', $exportHelper->getMemoryLimit() . 'M');
        set_time_limit(60000);
        ini_set('max_execution_time', 60000);
        ini_set('display_errors', 1);
        ini_set('output_buffering', 0);
        
        $bExportProductLink = TRUE;
        $process_error = 'no_errors';
        
        try {
            $_fStore = Mage::getModel('core/store')->load($this->getStoreId());
            $_fPath = Mage::helper('celexport')->getExportPath((int)$_SERVER['argv'][4]) . '/' . $_fStore->getWebsite()->getCode() . '/' . $_fStore->getCode();
            
            if (!is_dir($_fPath)) {
                $dir = @mkdir($_fPath, 0777, TRUE);
            }
            
            $filePath = $_fPath . '/' . 'export_chunk_' . $this->getProcessId() . "." . 'txt';
            
            $fh = fopen($filePath, 'ab');
            if (!$fh) {
                $exportHelper->logProfiler('Cannot create file from separate process.', (int)$_SERVER['argv'][4]);
                exit;
            }
            
            $item = Mage::getModel('celexport/cache')->getCollection()->addFieldToFilter('name', 'export_chunk_' . $this->getProcessId())->getLastItem();
            $rows = json_decode($item->getContent());
            $item->delete();
            $hasData = count($rows);
            
            $str = NULL;
            $ids = array();
            foreach ($rows as $row) {
                $ids[] = $row->entity_id;
            }
            
            //Prepare custom attributes list.
            $customAttributes = json_decode(Mage::getModel('celexport/cache')
                ->getCollection()
                ->addFieldToFilter('name', 'export_custom_fields')
                ->getFirstItem()
                ->getContent());
                
            $str = $exportHelper->getProductsData($ids, $customAttributes, $this->getStoreId());
            fwrite($fh, $str);
            fclose($fh);
        } catch (Exception $e) {
            $exportHelper->logProfiler('Caught exception: ' . $e->getMessage(), (int)$_SERVER['argv'][4]);
        }
        
        Mage::getModel('celexport/cache')
            ->setName('process_' . $this->getProcessId())
            ->setContent($process_error)
            ->save();
    }
    
}

$shell = new Celebros_Shell_ProcessChunkCol();
$shell->run();