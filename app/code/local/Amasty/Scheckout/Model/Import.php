<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Scheckout
 */
class Amasty_Scheckout_Model_Import extends Mage_Core_Model_Abstract
{
    protected static $_sessionKey = 'am_scheckout_import_process_%key%';
    
    protected $_rowsPerTransaction = 10000;
    
    protected $_modelsCols = array(
        'country' => array(
            'ip1_temp', 'ip2_temp', 'ip_from', 'ip_to', 'code', 'country'            
        ),
        'block' => array(
            'start_ip_num', 'end_ip_num', 'geoip_loc_id'
        ),
        'location' => array(
            'geoip_loc_id', 'country', 'region', 'city', 'postal_code',
            'latitude', 'longitude', 'dma_code', 'area_code'
        )
    );
    
    protected function getRowsCount($filePath){
        $linecount = 0;
        $handle = fopen($filePath, "r");
        while(!feof($handle)){
          $line = fgets($handle);
          $linecount++;
        }

        return $linecount;
    }
    
    protected function importItem($table, $tmpTableName, &$data){
        
        $write = Mage::getSingleton('core/resource')->getConnection('core_write');
        
        $query = 'insert into `' . $tmpTableName . '`'.
            '(`' . implode('`, `', $this->_modelsCols[$table]) . '`) VALUES '.
            '(?)'
        ;
        
        $query = $write->quoteInto($query, $data);
        
        $write->query($query);
    }
    
    protected function prepareImport($table){
        $write = Mage::getSingleton('core/resource')->getConnection('core_write');
        
        $targetTable = Mage::getSingleton('core/resource')
                ->getTableName('amscheckout/'.$table);
        
        $tmpTableName = uniqid($targetTable);
        
        $query = 'create table ' . $tmpTableName. ' like '. $targetTable;
        $write->query($query);
        
        $query = 'alter table ' . $tmpTableName. ' engine innodb';
        $write->query($query);
        
        return $tmpTableName;
    }
    
    protected function doneImport($table, $tmpTableName){
        
        $write = Mage::getSingleton('core/resource')->getConnection('core_write');
        
        $targetTable = Mage::getSingleton('core/resource')
                ->getTableName('amscheckout/'.$table);
        
        $query = 'delete from ' . $targetTable;
        $write->query($query);
        
        $query = 'insert into ' . $targetTable . ' select * from ' . $tmpTableName;
        $write->query($query);
        
    }
    
    protected function destroyImport($table, $tmpTableName){
        
        $write = Mage::getSingleton('core/resource')->getConnection('core_write');
        
        $query = 'drop table ' . $tmpTableName;
        $write->query($query);
        
        Mage::getSingleton('core/session')->setData(self::getSessionKey($table), NULL);
    }
    
    
    static function getSessionKey($table){
        return strtr(self::$_sessionKey, array(
            '%key%' => $table
        ));
    }
    
    function startProcess($table, $filePath, $ignoredLines = 0){
        
        $ret = array();
        
        $importProcess = array(
            'position' => 0,
            'tmp_table' => NULL,
            'rows_count' => $this->getRowsCount($filePath) - $ignoredLines,
            'current_row' => 0
        );

        if (($handle = fopen($filePath, "r")) !== FALSE) {
            
            $tmpTableName = $this->prepareImport($table);

            
            while ($ignoredLines > 0 && ($data = fgetcsv($handle, 0, ",")) !== FALSE) {
                $ignoredLines--;
            }
            

            $importProcess['position'] = ftell($handle);
            $importProcess['tmp_table'] = $tmpTableName;
            $ret = $importProcess;
        }

        Mage::getSingleton('core/session')->setData(self::getSessionKey($table), $importProcess);
            
        return $ret;
    }
    
    function doProcess($table, $filePath){
        $ret = array();
        if (($handle = fopen($filePath, "r")) !== FALSE) {
            
            $importProcess = Mage::getSingleton('core/session')->getData(self::getSessionKey($table));
            
            $write = Mage::getSingleton('core/resource')->getConnection('core_write');
            
            if ($importProcess){
                
                $tmpTableName = $importProcess['tmp_table'];
                
                try {
                    $position = $importProcess['position'];
                
                    fseek($handle, $position);
                    
                    $transactionIterator = 0;
                    
                    $write->beginTransaction();
                    
                    while (($data = fgetcsv($handle, 0, ",")) !== FALSE) {
                        
                        $this->importItem($table, $tmpTableName, $data);
                        
                        $transactionIterator++;

                        if ($transactionIterator >= $this->_rowsPerTransaction){
                            break;
                        }
                    }
                    
                    $write->commit();

                    $importProcess['current_row'] += $transactionIterator;

                    $importProcess['position'] = ftell($handle);

                    Mage::getSingleton('core/session')->setData(self::getSessionKey($table), $importProcess);
                    
                    $ret = $importProcess;
                    
                } catch (Exception $e) {
                    $write->rollback();
                    
                    $this->destroyImport($table, $tmpTableName);
                    
                    throw new Exception($e->getMessage());

                    
                }   
            }
            else
                throw new Exception('run start before');

        }

        return $ret;
    }
    
    
    function commitProcess($table){
        $ret = FALSE;
        $importProcess = Mage::getSingleton('core/session')->getData(self::getSessionKey($table));
        if ($importProcess){ 
            $tmpTableName = $importProcess['tmp_table'];
            
            try {
                
                Mage::app()->getConfig()
                    ->saveConfig('amscheckout/import/'.$table, 1)
                    ->reinit();

                $this->doneImport($table, $tmpTableName);
                
            } catch (Exception $e) {
                $this->destroyImport($table, $tmpTableName);
                
                throw new Exception($e->getMessage());
            }
            
            $this->destroyImport($table, $tmpTableName);

            $ret = TRUE;
        } else
            throw new Exception('run start before');
        
        return $ret;
    }
    
    function isDone(){
        return Mage::getStoreConfig('amscheckout/import/country') == 1 &&
               Mage::getStoreConfig('amscheckout/import/block') == 1 &&
               Mage::getStoreConfig('amscheckout/import/location') == 1 ;
                
    }
    

}
?>