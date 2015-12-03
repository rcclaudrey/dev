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
ini_set('memory_limit', Mage::helper('celexport/export')->getMemoryLimit() . 'M');
set_time_limit(18000);
ini_set('max_execution_time', 18000);
ini_set('display_errors', 1);
ini_set('output_buffering', 0);

//include_once("createZip.php");
class Celebros_Celexport_Model_ObserverLarge extends Celebros_Celexport_Model_Exporter
{
    protected static $_profilingResults;
    protected $bExportProductLink = TRUE;
    protected $_product_entity_type_id = NULL;
    protected $_category_entity_type_id = NULL;
    protected $prod_file_name = "source_products";
    protected $_categoriesForStore = FALSE;
    
    function __construct()
    {
        $this->_read=Mage::getSingleton('core/resource')->getConnection('core_read');
        $this->_product_entity_type_id = $this->get_product_entity_type_id();
        $this->_category_entity_type_id = $this->get_category_entity_type_id();
    }
    
    public function exportProdIdsByAttributeValue($attribute_code, $attribute_value, $store_id = 0, $filename)
    {
        $collection = Mage::getModel('catalog/product')->getCollection();
        $collection->setStoreId($store_id)
            ->addStoreFilter($store_id)
            ->addAttributeToFilter($attribute_code, $attribute_value);
        
        $fh = $this->create_file($filename);
        if (!$fh) {
            $this->comments_style('error', 'Could not create the file ' . $filename . ' path', 'problem with file');
            $this->logProfiler('Could not create the file ' . $filename . ' path');
            return;
        }
        
        $str = "^entity_id^\r\n";
        
        foreach ($collection as $item) {
            $str .= "^" . $item->getEntityId() . "^" . "\r\n";
        }
        
        $this->write_to_file($str, $fh);
        fclose($fh);
    }
    
    public function export_celebros($webAdmin, $exportProcessId = 0)
    {
        //self::startProfiling(__FUNCTION__);
        $this->_exportProcessId = $exportProcessId;
        $this->isWebRun = $webAdmin;
         
        foreach (Mage::app()->getStores() as $store) {
            if (!$this->isStoreExportEnabled($store->getStoreId())) {
                $this->comments_style('info', "Export not enabled for store view: {$store->getName()}", 'STORE');
                continue;
            }
            
            $this->_fStore_id = $store->getStoreId();
            $this->export_config($this->_fStore_id);
            
            $this->_fileNameZip = Mage::getStoreConfig('celexport/export_settings/zipname', $this->_fStore_id);
            
            $this->comments_style('section', "Store code: {$this->_fStore_id}, name: {$store->getName()}", 'STORE');
            $this->comments_style('section', "Zip file name: {$this->_fileNameZip}", 'STORE');
           
            //Resetting store categories mapping.
            $this->_categoriesForStore = FALSE;
            $this->_categoriesForStore = implode(',', $this->_getAllCategoriesForStore());
            
            $this->logProfiler('===============');
            $this->logProfiler('Starting Export');
            $this->logProfiler('===============', NULL, TRUE);
            $this->logProfiler("Store code: {$this->_fStore_id}, name: {$store->getName()}");
            $this->logProfiler("Zip file name: {$this->_fileNameZip}");
            $this->logProfiler('Mem usage: ' . memory_get_usage(TRUE), NULL, TRUE);
            
            $this->comments_style('icon', "Memory usage: " . memory_get_usage(TRUE), 'icon');
            $this->comments_style('icon', 'Exporting tables', 'icon');
            $this->comments_style('info', "Memory usage: " . memory_get_usage(TRUE), 'info');
            
            $this->logProfiler('Exporting tables');
            $this->logProfiler('----------------', NULL, TRUE);
            
            $this->export_tables();
            
            $this->comments_style('info', "Memory usage: " . memory_get_usage(TRUE), 'info');
            
            //Only run export products if there are categories assigned to the current store view.
            if ($this->_categoriesForStore && count($this->_categoriesForStore)) {
                $this->comments_style('icon', 'Exporting products', 'icon');
                $this->comments_style('info', "Memory usage: " . memory_get_usage(TRUE), 'info');
                
                $this->logProfiler('Writing products file');
                $this->logProfiler('---------------------', NULL, TRUE);
                
                $this->export_store_products();
            }
            
            //Running over the products that aren't assigned to a category separately.
            $this->comments_style('icon', 'Exporting category-less products' , 'icon');
            $this->comments_style('info', "Memory usage: " . memory_get_usage(TRUE), 'info');
            
            $this->logProfiler('Writing category-less products file');
            $this->logProfiler('-----------------------------------', NULL, TRUE);
            $this->export_categoryless_products();
            
            $this->comments_style('icon', 'Creating ZIP file' , 'icon');
            $this->comments_style('info', "Memory usage: " . memory_get_usage(TRUE), 'info');
            
            $this->logProfiler('Creating ZIP file');
            $this->logProfiler('-----------------', NULL, TRUE);
            
            $zipFilePath = $this->zipLargeFiles();
            
            $this->comments_style('icon', 'Checking FTP upload', 'icon');
            $this->comments_style('info', "Memory usage: " . memory_get_usage(TRUE), 'info');
            
            if($this->_fType==="ftp" && $this->_bUpload) {
                $this->comments_style('info', 'Uploading export file', 'info');
                
                $ftpRes = $this->ftpfile($zipFilePath);
                if(!$ftpRes) {
                    $this->comments_style('info', "Could not upload " . $zipFilePath . ' to ftp', 'info');
                    $this->logProfiler('FTP upload ERROR', NULL, TRUE);
                } else {
                    $this->logProfiler('FTP upload success', NULL, TRUE);
                } 
            } else {
                $this->comments_style('info', 'No need to upload export file', 'info');
                $this->logProfiler('No need to upload export file', NULL, TRUE);
            }
            
            $this->comments_style('icon', 'Finished' , 'icon');
            $this->comments_style('info', "Memory usage: " . memory_get_usage(TRUE), 'info');
            $this->comments_style('info', "Memory peek usage: " . memory_get_peak_usage(TRUE), 'info');
            $this->comments_style('icon', date('Y/m/d H:i:s') , 'icon');
            
            $this->logProfiler('Mem usage: ' . memory_get_usage(TRUE), NULL, TRUE);
            $this->logProfiler('Mem peek usage: ' . memory_get_peak_usage(TRUE), NULL, TRUE);
            
            //self::stopProfiling(__FUNCTION__);
            
            //$html = self::getProfilingResultsString();
            //$this->log_profiling_results($html);
            //echo $html;
        }
    }
    
    protected function getWebsiteId($storeId)
    {
        return (int)Mage::getModel('core/store')->load($storeId)->getWebsiteId();
    }
    
    /*
     * Wrapper function around export_products(), that defines a specific sql query and file name for use when exporting store
     * specific products.
     */
    protected function export_store_products()
    {
        $table = $this->getTableName("catalog_product_entity");
        $categoryProductsTable = $this->getTableName("catalog_category_product");
        $catalogProductWebsite = $this->getTableName("catalog_product_website");
        $rootCategoryId = $this->_fStore->getRootCategoryId();
        $sql = "SELECT DISTINCT(entity_id), type_id, sku FROM {$table}
            LEFT JOIN (`{$categoryProductsTable}`) ON (`{$categoryProductsTable}`.`category_id` IN ({$this->_categoriesForStore}))
            LEFT JOIN (`{$catalogProductWebsite}`) ON ({$table}.`entity_id` = `{$catalogProductWebsite}`.`product_id`)
        WHERE {$table}.entity_type_id ={$this->_product_entity_type_id}
            AND {$table}.`entity_id` = `{$categoryProductsTable}`.`product_id`
            AND `{$catalogProductWebsite}`.`website_id` =" . $this->getWebsiteId($this->_fStore_id);
            
        if (!Mage::getStoreConfig('celexport/export_settings/rootcat_products_export')) {
            $sql .= " AND `{$categoryProductsTable}`.`category_id` != {$rootCategoryId}";
        }
        
        $this->export_products($sql, $this->prod_file_name);
    }   
    
    /*
     * Wrapper function around export_products(), that defines a specific sql query and file name for use when exporting products
     * that aren't assigned to any category (thus, not appearing under any store either).
     */
    protected function export_categoryless_products()
    {
        $table = $this->getTableName("catalog_product_entity");
        $categoryProductsTable = $this->getTableName("catalog_category_product");
        $catalogProductWebsite = $this->getTableName("catalog_product_website");
        $rootCategoryId = $this->_fStore->getRootCategoryId();
        $sql = "SELECT DISTINCT(entity_id), type_id, sku FROM {$table}
            LEFT JOIN (`{$categoryProductsTable}`) ON ({$table}.`entity_id` = `{$categoryProductsTable}`.`product_id`)
            LEFT JOIN (`{$catalogProductWebsite}`) ON ({$table}.`entity_id` = `{$catalogProductWebsite}`.`product_id`)
        WHERE (`{$categoryProductsTable}`.`product_id` IS NULL OR `{$categoryProductsTable}`.`category_id` NOT IN ({$this->_getCategoriesForStore()}))
            AND {$table}.entity_type_id = {$this->_product_entity_type_id}
            AND `{$catalogProductWebsite}`.`website_id` = " . $this->getWebsiteId($this->_fStore_id);           
                    
        $this->export_products($sql, 'categoryless_products');
    }
    
    protected function log_profiling_results($html)
    {
        $fh = $this->create_file("profiling_results.log", "html");
        $this->write_to_file($html, $fh);
    }
    
    protected function get_status_attribute_id()
    {
        $table = $this->getTableName("eav_attribute");
        $sql = "SELECT attribute_id
        FROM {$table}
        WHERE entity_type_id ={$this->_product_entity_type_id} AND attribute_code='status'";
        
        return $this->_read->fetchOne($sql);
    }
    
    protected function get_product_entity_type_id()
    {
        $table = $this->getTableName("eav_entity_type");
        $sql = "SELECT entity_type_id
        FROM {$table}
        WHERE entity_type_code='catalog_product'";
        
        return $this->_read->fetchOne($sql);
    }
    
    protected function get_category_entity_type_id()
    {
        $table = $this->getTableName("eav_entity_type");
        $sql = "SELECT entity_type_id
        FROM {$table}
        WHERE entity_type_code='catalog_category'";
        
        return $this->_read->fetchOne($sql);
    }
    
    protected function get_visibility_attribute_id()
    {
        $table = $this->getTableName("eav_attribute");
        $sql = "SELECT attribute_id
        FROM {$table}
        WHERE entity_type_id ={$this->_product_entity_type_id} AND attribute_code='visibility'";
        
        return $this->_read->fetchOne($sql);
    }
    
    protected function get_category_name_attribute_id()
    {
        $table = $this->getTableName("eav_attribute");
        $sql = "SELECT attribute_id
        FROM {$table}
        WHERE entity_type_id ={$this->_category_entity_type_id} AND attribute_code='name'";
        
        return $this->_read->fetchOne($sql);
    }
    
    protected function get_category_is_active_attribute_id()
    {
        $table = $this->getTableName("eav_attribute");
        $sql = "SELECT attribute_id
        FROM {$table}
        WHERE entity_type_id ={$this->_category_entity_type_id} AND attribute_code='is_active'";
        
        return $this->_read->fetchOne($sql);
    }
    
    protected function export_extra_tables()
    {
        $this->comments_style('icon', "Exporting extra tables", 'icon');
        $read = Mage::getModel('core/resource')->getConnection('core_read');
        $extraTablesData = Mage::getStoreConfig('celexport/export_settings/extra_tables', 0);
        $extraTables = explode("\n", $extraTablesData);
        foreach ($extraTables as $table) {
            if (trim($table)=='') {
                continue;
            }
            
            try {
                $tableName=$this->getTableName(trim($table));
            } catch (Exception $ex) {
                $this->comments_style('error', "Table '{$table}' does not exist", 'error');
                continue;
            }
            
            $tableExists = $read->isTableExists($tableName);
            if ($tableExists) {
                $this->comments_style('info', "Exporting table '{$tableName}'", 'info');
                $query = $read->select()->from($tableName, array('*'));
                $this->export_table($query, $tableName);
            } else {
                $this->comments_style('error', "Table '{$table}'='{$tableName}' does not exist", 'error');
            }
        }
    }
    
    protected function export_tables() {
        //self::startProfiling(__FUNCTION__);
        $read = Mage::getModel('core/resource')->getConnection('core_read');
        
        /*----- catalog_eav_attribute.txt -----*/
        $table = $this->getTableName("catalog_eav_attribute");
        $query = $read->select()
            ->from($table,
                    array('attribute_id', 'is_searchable', 'is_filterable', 'is_comparable'));
        $this->export_table($query, "catalog_eav_attribute");
        
        /*----- attributes_lookup.txt -----*/
        $table = $this->getTableName("eav_attribute");
        $this->logProfiler("START {$table}");
        $this->logProfiler('Mem usage: ' . memory_get_usage(TRUE));
        $query = $read->select()
            ->from($table,
                array('attribute_id', 'attribute_code', 'backend_type', 'frontend_input'))
            ->where('entity_type_id = ?', $this->_product_entity_type_id);
        $this->export_attributes_table($query, "attributes_lookup");
        $this->logProfiler("FINISH {$table}");
        $this->logProfiler('Mem usage: ' . memory_get_usage(TRUE), NULL, TRUE);
        
        /*----- catalog_product_entity.txt -----*/
        $table = $this->getTableName("catalog_product_entity");
        $this->logProfiler("START {$table}");
        $this->logProfiler('Mem usage: ' . memory_get_usage(TRUE));
        $categories = implode(',', $this->_getAllCategoriesForStore());
        $categoryProductsTable = $this->getTableName("catalog_category_product");
        
        $query = $read->select()
            ->from($table,
                array('entity_id', 'type_id', 'sku'))
            ->where("`{$table}`.`entity_type_id` = ?", $this->_product_entity_type_id)
            ->joinLeft($categoryProductsTable, 
                        "`{$table}`.`entity_id` = `{$categoryProductsTable}`.`product_id`",
                        array())
            /*->where("`{$categoryProductsTable}`.`category_id` IN ({$categories})")*/
            ->group('entity_id');
        $this->export_table($query, "catalog_product_entity");
        $this->logProfiler("FINISH {$table}");
        $this->logProfiler('Mem usage: ' . memory_get_usage(TRUE), NULL, TRUE);
        
        /*----- disabled_products.txt -----*/
        $this->exportProdIdsByAttributeValue('status', Mage_Catalog_Model_Product_Status::STATUS_DISABLED,
            $this->_fStore_id, 'disabled_products');
        
        /*----- not_visible_individually_products.txt -----*/
        $this->exportProdIdsByAttributeValue('visibility', Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE,
            $this->_fStore_id, 'not_visible_individually_products');
        
        /*----- catalog_product_entity_varchar.txt -----*/    
        $table = $this->getTableName("catalog_product_entity_varchar");     
        $this->logProfiler("START {$table}");
        $this->logProfiler('Mem usage: ' . memory_get_usage(TRUE));
        $sql = $read->select()
            ->from($table, 
                array('entity_id', 'value', 'attribute_id'))
            ->where('entity_type_id = ?', $this->_product_entity_type_id);
        $this->export_product_att_table($sql, "catalog_product_entity_varchar");
        $this->logProfiler("FINISH {$table}");
        $this->logProfiler('Mem usage: ' . memory_get_usage(TRUE), NULL, TRUE);
        
        /*----- catalog_product_entity_int.txt -----*/
        $table = $this->getTableName("catalog_product_entity_int");
        $this->logProfiler("START {$table}");
        $this->logProfiler('Mem usage: ' . memory_get_usage(TRUE));
        $query = $read->select()
            ->from($table,
                    array('entity_id', 'value', 'attribute_id'))
            ->where('entity_type_id = ?', $this->_product_entity_type_id);
        $this->export_product_att_table($query, "catalog_product_entity_int");
        $this->logProfiler("FINISH {$table}");
        $this->logProfiler('Mem usage: ' . memory_get_usage(TRUE), NULL, TRUE);
        
        /*----- catalog_product_entity_text.txt -----*/
        $table = $this->getTableName("catalog_product_entity_text");        
        $this->logProfiler("START {$table}");
        $this->logProfiler('Mem usage: ' . memory_get_usage(TRUE));
        $query = $read->select()
            ->from($table,
                    array('entity_id', 'value', 'attribute_id'))
            ->where('entity_type_id = ?', $this->_product_entity_type_id);
        $this->export_product_att_table($query, "catalog_product_entity_text");
        $this->logProfiler("FINISH {$table}");
        $this->logProfiler('Mem usage: ' . memory_get_usage(TRUE), NULL, TRUE);
        
        /*----- catalog_product_entity_decimal.txt -----*/
        $table = $this->getTableName("catalog_product_entity_decimal");     
        $this->logProfiler("START {$table}");
        $this->logProfiler('Mem usage: ' . memory_get_usage(TRUE));
        $query = $read->select()
            ->from($table,
                    array('entity_id', 'value', 'attribute_id'))
            ->where('entity_type_id = ?', $this->_product_entity_type_id);
        $this->export_product_att_table($query, "catalog_product_entity_decimal");
        $this->logProfiler("FINISH {$table}");
        $this->logProfiler('Mem usage: ' . memory_get_usage(TRUE), NULL, TRUE);
        
        /*----- catalog_product_entity_datetime.txt -----*/
        $table = $this->getTableName("catalog_product_entity_datetime");
        $this->logProfiler("START {$table}");
        $this->logProfiler('Mem usage: ' . memory_get_usage(TRUE));
        $query = $read->select()
            ->from($table,
                    array('entity_id', 'value', 'attribute_id'))
            ->where('entity_type_id = ?', $this->_product_entity_type_id);
        $this->export_product_att_table($query, "catalog_product_entity_datetime");
        $this->logProfiler("FINISH {$table}");
        $this->logProfiler('Mem usage: ' . memory_get_usage(TRUE), NULL, TRUE);
        
        /*----- eav_attribute_option_value.txt -----*/
        $table = $this->getTableName("eav_attribute_option_value");
        $this->logProfiler("START {$table}");
        $this->logProfiler('Mem usage: ' . memory_get_usage(TRUE));
        $query = $read->select()
            ->from($table,
                    array('option_id', 'value'));
        $this->export_table($query, "eav_attribute_option_value", array('option_id'));
        $this->logProfiler("FINISH {$table}");
        $this->logProfiler('Mem usage: ' . memory_get_usage(TRUE), NULL, TRUE);
        
        /*----- eav_attribute_option.txt -----*/
        $table = $this->getTableName("eav_attribute_option");       
        $this->logProfiler("START {$table}");
        $this->logProfiler('Mem usage: ' . memory_get_usage(TRUE));
        $query = $read->select()
            ->from($table,
                array('option_id', 'attribute_id'));
        $this->export_table($query, "eav_attribute_option");
        $this->logProfiler("FINISH {$table}");
        $this->logProfiler('Mem usage: ' . memory_get_usage(TRUE), NULL, TRUE);
        
        $table = $this->getTableName("catalog_category_product");       
        $this->logProfiler("START {$table}");
        $this->logProfiler('Mem usage: ' . memory_get_usage(TRUE));
        $categories = implode(',', $this->_getAllCategoriesForStore());
        $query = $read->select()
            ->from($table,
                    array('category_id', 'product_id'))
            ->where("`category_id` IN ({$categories})");
        $this->export_table($query, "catalog_category_product");
        $this->logProfiler("FINISH {$table}");
        $this->logProfiler('Mem usage: ' . memory_get_usage(TRUE), NULL, TRUE);
        
        $table = $this->getTableName("catalog_category_entity");        
        $this->logProfiler("START {$table}");
        $this->logProfiler('Mem usage: ' . memory_get_usage(TRUE));
        $categories = implode(',', $this->_getAllCategoriesForStore());
        $query = $read->select()
            ->from($table,
                    array('entity_id', 'parent_id', 'path'))
            ->where("`entity_id` IN ({$categories})");
        $this->export_table($query, "catalog_category_entity");
        $this->logProfiler("FINISH {$table}");
        $this->logProfiler('Mem usage: ' . memory_get_usage(TRUE), NULL, TRUE);
        
        $table = $this->getTableName("catalog_category_entity_varchar");
        $this->logProfiler("START {$table}");
        $this->logProfiler('Mem usage: ' . memory_get_usage(TRUE));
        $name_attribute_id = $this->get_category_name_attribute_id();
        $categories = implode(',', $this->_getAllCategoriesForStore());
        $query = $read->select()
            ->from($table,
                    array('entity_id', 'value'))
            ->where('attribute_id = ?', $name_attribute_id)
            ->where("`entity_id` IN ({$categories})");
            
        $this->export_table($query, "category_lookup", array('entity_id'));
        $this->logProfiler("FINISH {$table}");
        $this->logProfiler('Mem usage: ' . memory_get_usage(TRUE), NULL, TRUE);
        
        $table = $this->getTableName("catalog_category_entity_int");
        $this->logProfiler("START {$table}");
        $this->logProfiler('Mem usage: ' . memory_get_usage(TRUE));
        $is_active_attribute_id = $this->get_category_is_active_attribute_id();
        $categories = implode(',', $this->_getAllCategoriesForStore());
        $query = $read->select()
            ->from($table,
                    array('entity_id'))
            ->where('attribute_id = ?', $is_active_attribute_id)
            ->where('value = 0')
            ->where('entity_type_id = ?', $this->_category_entity_type_id)
            ->where("`entity_id` IN ({$categories})");
            
        $this->export_table($query, "disabled_categories", array('entity_id'));
        $this->logProfiler("FINISH {$table}");
        $this->logProfiler('Mem usage: ' . memory_get_usage(TRUE), NULL, TRUE);
        
        $table = $this->getTableName("catalog_product_super_link");     
        $this->logProfiler("START {$table}");
        $this->logProfiler('Mem usage: ' . memory_get_usage(TRUE));
        $query = $read->select()
            ->from($table,
                    array('product_id', 'parent_id'));
                    
        $this->export_table($query, "catalog_product_super_link");
        $this->logProfiler("FINISH {$table}");
        $this->logProfiler('Mem usage: ' . memory_get_usage(TRUE), NULL, TRUE);
        
        $table = $this->getTableName("catalog_product_relation");
        $this->logProfiler("START {$table}");
        $this->logProfiler('Mem usage: ' . memory_get_usage(TRUE));
        $query = $read->select()
            ->from($table,
                    array('parent_id', 'child_id'));
        
        $this->export_table($query, "catalog_product_relation");
        $this->logProfiler("FINISH {$table}");
        $this->logProfiler('Mem usage: ' . memory_get_usage(TRUE), NULL, TRUE);
        
        $table = $this->getTableName("catalog_product_super_attribute");
        $this->logProfiler("START {$table}");
        $this->logProfiler('Mem usage: ' . memory_get_usage(TRUE));
        $query = $read->select()
            ->from($table,
                    array('product_id', 'attribute_id'));
                    
        $this->export_table($query, "catalog_product_super_attribute");
        $this->logProfiler("FINISH {$table}");
        $this->logProfiler('Mem usage: ' . memory_get_usage(TRUE), NULL, TRUE);      
        
        $table = $this->getTableName("celebros_mapping");
        $this->logProfiler("START {$table}");
        $this->logProfiler('Mem usage: ' . memory_get_usage(TRUE));
        $query = $read->select()
            ->from($table,
                    array('xml_field', 'code_field'));
                    
        $this->export_table($query, "celebros_mapping");
        $this->logProfiler("FINISH {$table}");
        $this->logProfiler('Mem usage: ' . memory_get_usage(TRUE), NULL, TRUE);      
        
        $table = $this->getTableName("review_entity");
        $product_entity_id = $read->select()
                                    ->from($table, array('entity_id'))
                                    ->where("`entity_code` = 'product'")
                                    ->query()->fetch();
        
        $table = $this->getTableName("review_entity_summary");
        $this->logProfiler("START {$table}");
        $this->logProfiler('Mem usage: ' . memory_get_usage(TRUE));
        $query = $read->select()
            ->from($table,
                    array('entity_pk_value', 'reviews_count', 'rating_summary'))
            ->where("`entity_type` = '{$product_entity_id['entity_id']}'");
        
        $this->export_table($query, "review_entity", array('entity_pk_value'));
        $this->logProfiler("FINISH {$table}");
        $this->logProfiler('Mem usage: ' . memory_get_usage(TRUE), NULL, TRUE);  
        
        $table = $this->getTableName("catalog_product_website");
        $this->logProfiler("START {$table}");
        $this->logProfiler('Mem usage: ' . memory_get_usage(TRUE));
        $query = $read->select()
            ->from($table,
                    array('product_id'))
            ->where('website_id=?', $this->getWebsiteId($this->_fStore_id));
        
        $this->export_table($query, "catalog_product_website");
        $this->logProfiler("FINISH {$table}");
        $this->logProfiler('Mem usage: ' . memory_get_usage(TRUE), NULL, TRUE);
        
        $this->export_extra_tables();
        //self::stopProfiling(__FUNCTION__);
    }
    
    protected function export_table_rows($sql, $fields, $fh)
    {
        $str = "";
        
        $query = $sql->query();
        $rowCount = 0;
        $processedRows = array();
        
        while ($row = $query->fetch()) {
            //$this->logProfiler("Block read start ({$this->_limit} products");
            //$this->logProfiler('Mem usage: '.memory_get_usage(TRUE));
            
            //remember all the rows we're processing now, so we won't go over them again when we iterate over the default store.
            if (isset($fields)) {
                $concatenatedRow = '';
                foreach ($fields as $field) {
                    $concatenatedRow .= $row[$field] . '-';
                }
                
                $processedRows[] = substr($concatenatedRow, 0, -1);
            }
            
            $str .= "^" . implode("^" . $this->_fDel . "^", $row) . "^" . "\r\n";
            $rowCount++;
            
            if (($rowCount%1000)==0) {
                //$this->logProfiler("Write block start");
                $this->write_to_file($str , $fh);
                //$this->logProfiler("Write block end");
                $str="";
            }
        }
        
        if (($rowCount%1000)!=0) {
            //$this->logProfiler("Write last block start");
            $this->write_to_file($str , $fh);
            //$this->logProfiler("Write last block end");
        }
        
        $this->logProfiler("Total rows: {$rowCount}");
        
        return $processedRows;
    }
    
    protected function write_headers($sql, $fh)
    {   
        $header = "^";
        $columns = $sql->getPart('columns');
        $fields = array();
        foreach ($columns as $column) {
            if ($column[1] != '*') {
                $fields[] = $column[1];
            } else {
                $read = Mage::getModel('core/resource')->getConnection('core_read');
                $fields = array_merge($fields, array_keys($read->describeTable($this->getTableName($columns[0][0]))));
            }
        }
        $header .= implode("^" . $this->_fDel . "^", $fields);
        $header .= "^" . "\r\n";
        $this->write_to_file($header, $fh);
        
        return $columns;
    }
    
    /* This is a separate function because of two changes from export_table(): 
     * 1. We're adding another column header at the start for the frontend_label (which isn't selected in the first run)
     * 2. On the first run, we've added a join statement to get the labels from eav_attribute_label. The second run covers all
     * cases where eav_attribute_label didn't have a value for a specific attribute.
     */
    protected function export_attributes_table($sql, $filename)
    {
        $fh = $this->create_file($filename);
        if (!$fh) {
            $this->comments_style('error', 'Could not create the file ' . $filename . ' path', 'problem with file');
            $this->logProfiler('Could not create the file ' . $filename . ' path');
            return;
        }
        
        //Adding another column header before the call to write_headers().
        $columns = $sql->getPart('columns');
        $sql->columns('frontend_label');
        $this->write_headers($sql, $fh);
        $sql->setPart('columns', $columns);
        
        $sql->limit(100000000, 0);
        
        //Preparing the select object for the second query.
        $secondSql = clone($sql);
        
        //Adding a join statement to the first run alone, to get labels from eav_attribute_label.
        $table = $sql->getPart('from');
        $table = array_shift($table);
        $labelTable = $this->getTableName("eav_attribute_label");
        $sql->joinLeft($labelTable, 
                "{$table['tableName']}.`attribute_id` = `{$labelTable}`.`attribute_id`
                AND `{$labelTable}`.`store_id` = {$this->_fStore_id}",
                array('value'))
            ->where("`{$labelTable}`.`value` IS NOT NULL")
            ->group('attribute_id');
        
        //Process the rows that are covered by eav_attribute_label.
        $processedRows = $this->export_table_rows($sql, array('attribute_id'), $fh);
        
        //run a second time with only ids that are not in the list from the first run.
        $secondSql->columns('frontend_label');
        if (count($processedRows)) {
            $secondSql->where("`attribute_id` NOT IN (?)", $processedRows);
        }
        
        $this->export_table_rows($secondSql, NULL, $fh);
        
        fclose($fh);
        //self::stopProfiling(__FUNCTION__. "({$filename})");
    }
    
    protected function export_table($sql, $filename, $main_fields = NULL)
    {
        $fh = $this->create_file($filename);
        if (!$fh) {
            $this->comments_style('error', 'Could not create the file ' . $filename . ' path', 'problem with file');
            $this->logProfiler('Could not create the file ' . $filename . ' path');
            return;
        }
        
        $this->write_headers($sql, $fh);
        
        $sql->limit(100000000, 0);
        
        //This part is only for tables that should be run twice - once with the store view, and again with the default.
        if (isset($main_fields)) {
            //preparing the query for the second run on the default store view.
            $secondSql = clone($sql);
            
            //On the first run, we'll only get the current store view.
            $sql->where('store_id = ?', $this->_fStore_id);
        }
        
        //Run the actual process of getting the rows and inserting them to the file,
        // and output the list of rows you covered to $processedRows.
        $processedRows = $this->export_table_rows($sql, $main_fields, $fh);
        
        //This part is only for tables that should be run twice - once with the store view, and again with the default.
        if (isset($main_fields)) {
            //Specifying the default store view.
            $secondSql->where('store_id = 0');
            
            //Only add the where statement in case items were found in the first run.
            if (count($processedRows)) {
                $concat_fields = implode('-', $main_fields);
                $secondSql->where("CONCAT({$concat_fields}) NOT IN (?)", $processedRows);
            }
            
            //Run the actual process of getting each row again, this time selecting rows with the default store view.
            $this->export_table_rows($secondSql, NULL, $fh);
        }
        
        fclose($fh);
        //self::stopProfiling(__FUNCTION__. "({$filename})");
    }
    
    /*
     * This version of the export_table function is meant for entity attribute tables, that have store view specific values.
     * Differences:
     * 1. We check whether the current store view has any categories assigned, and return nothing if it does not.
     * 2. We've added a join statement to only get rows that correspond to products that are assigned to categories that are 
     * under the current store view.
     * 3. Before running export_table_rows() for the first time, we execute the query, and withdraw a list of rows that will
     * be covered once the first run is complete. We then use that list in to exclude those rows from the second run. This is
     * essential because we have to include some columns (entity_id, attribute_id) that might not be in the select statement.
     */
    protected function export_product_att_table($sql, $filename)
    {
        
        $fh = $this->create_file($filename);
        if (!$fh) {
            $this->comments_style('error','Could not create the file ' . $filename . ' path','problem with file');
            $this->logProfiler('Could not create the file ' . $filename . ' path');
            return;
        }
        
        $columns = $this->write_headers($sql, $fh);
        
        $sql->limit(100000000, 0);
            
        //Get Relevant Categories for the query.
        $categoriesForStore = implode(',', $this->_getAllCategoriesForStore());
        
        //Don't run the query at all if no categories were found to match the current store view.
        if (!$categoriesForStore || !count($categoriesForStore)) {
            $this->logProfiler("Total rows: 0");
            fclose($fh);
            return;
        }
        
        $productCollection = Mage::getResourceModel('catalog/product_collection')->addStoreFilter($this->_fStore_id)->getColumnValues('entity_id');
        $relevant_products = implode(',', $productCollection);
        
        $table = $sql->getPart('from');
        $table = array_shift($table);
        $sql->where("{$table['tableName']}.`entity_id` IN ({$relevant_products})");
        
        $secondSql = clone($sql);
        
        $sql->where('`store_id` = ?', $this->_fStore_id);
        
        //Get list of rows with this specific store view, to exclude when running on the default store view.
        $sql->columns('entity_id');
        $sql->columns('attribute_id');
        $query = $sql->query();
        $processedRows = array();
        while ($row = $query->fetch()) {
            $processedRows[] = $row['attribute_id'] . '-' . $row['entity_id'];
        }
        $sql->setPart('columns', $columns);
        $sql->order('entity_id', 'ASC');
        
        //Run the query on each row and save results to the file.
        $this->export_table_rows($sql, NULL, $fh);
        
        //Prepare the second query.
        $secondSql->where('store_id = 0');
        if (count($processedRows)) {
            $secondSql->where("CONCAT(`attribute_id`, '-', `entity_id`) NOT IN (?)", $processedRows);
        }
        
        $secondSql->order('entity_id', 'ASC');
        
        //Run for the second time, now with the default store view.
        $this->export_table_rows($secondSql, NULL, $fh);
        
        fclose($fh);
        //self::stopProfiling(__FUNCTION__. "({$filename})");
    }
    
    protected function create_file($name, $ext = "txt")
    {
        //self::startProfiling(__FUNCTION__);
        try {
            if (!is_dir($this->_fPath)) {
                $dir = mkdir($this->_fPath, 0777, TRUE);
            }
            $filePath = $this->_fPath . DS . $name . "." . $ext;
            $fh = fopen($filePath, 'wb');       
        } catch (Exception $e) {
            $this->comments_style('error', 'Could not create export directory or files.', 'file permissions');
            $this->logProfiler('Failed creating the export files or directory.');
            return;
        }
        //self::stopProfiling(__FUNCTION__);
        return $fh;
    }
    
    protected function write_to_file($str, $fh)
    {
        //self::startProfiling(__FUNCTION__);
        fwrite($fh, $str);
        
        //self::stopProfiling(__FUNCTION__);
    }
    
    public function zipLargeFiles()
    {
        //self::startProfiling(__FUNCTION__);
        
        $out = FALSE;
        $zipPath = $this->_fPath . DIRECTORY_SEPARATOR . $this->_fileNameZip;
        
        try {
            $dh = opendir($this->_fPath);
        
        } catch (Exception $e) {
            $this->comments_style('error', 'Could not open folder for archiving.', 'problem with folder');
            $this->logProfiler('Could not open folder for archiving.');
            return;
        }
        
        $filesToZip = array();
        while (($item = readdir($dh)) !== FALSE && !is_null($item)) {
            $filePath = $this->_fPath . DIRECTORY_SEPARATOR . $item;
            $ext = pathinfo($filePath, PATHINFO_EXTENSION);
            if (is_file($filePath) && ($ext == "txt" || $ext == "log")) {
                $filesToZip[] = $filePath;
            }
        }
        
        for ($i=0; $i < count($filesToZip); $i++) {
            $filePath = $filesToZip[$i];
            $out = $this->zipLargeFile($filePath, $zipPath);
        }
        
        //self::stopProfiling(__FUNCTION__);
        return $out ? $zipPath : FALSE;
    }
    
    public function zipLargeFile($filePath, $zipPath)
    {
        //self::startProfiling(__FUNCTION__);
        
        $out = FALSE;
        
        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE) == TRUE) {
            $fileName = basename($filePath);
            $out = $zip->addFile($filePath, basename($filePath));
            if (!$out) {
                throw new Exception("Could not add file '{$fileName}' to_zip_file");
            }
            
            $zip->close();
            $ext = pathinfo($fileName, PATHINFO_EXTENSION);
            if ($ext != "log") {
                unlink($filePath);
            }
        } else {
            throw new Exception("Could not create zip file");
        }
        
        //self::stopProfiling(__FUNCTION__);
        return $out;
    }
    
    /**
     * Deprecated in favor of _getAllCategoriesForStore(), that gets the root categories as well.
     */ 
    protected function _getCategoriesForStore()
    {
        if (!$this->_categoriesForStore) {
            $rootCategoryId = $this->_fStore->getRootCategoryId();
            $rootCategory = Mage::getModel('catalog/category')->load($rootCategoryId);
            $rootResource = $rootCategory->getResource();
            $this->_categoriesForStore = implode(',', $rootResource->getChildren($rootCategory));
        }
        return $this->_categoriesForStore;
    }
    
    /*
     * This function gets root categories too, as well as disabled categories.
     * We've left these in so as not to create holes in the tables export.
     */
    protected function _getAllCategoriesForStore()
    {
        $read = Mage::getModel('core/resource')->getConnection('core_read');
        $table = $this->getTableName("catalog_category_entity");
        $sql2 = $read->select()->from($table, array('entity_id', 'path'));
        
        $results = $read->fetchPairs($sql2);
        $rootCategoryId = $this->_fStore->getRootCategoryId();
        $categories = array();
        foreach ($results as $entity_id => $path) {
            $path = explode('/', $path);
            if (count($path) > 1) {
                if ($path[1] == $rootCategoryId) {
                    $categories[] = $entity_id;
                }
            } else {
                $categories[] = $entity_id;
            }
        }
        
        return $categories;
    }
    
    protected function is_process_running($PID)
    {
        exec("ps $PID", $ProcessState);
        return (count($ProcessState) >= 2);
    }
    
    protected function _getCustomAttributes()
    {
        $eav_attributes = $this->getTableName("eav_attribute");
        $catalog_eav_attribute = $this->getTableName("catalog_eav_attribute");
        $sql = "SELECT `attribute_code` FROM `{$eav_attributes}` 
            LEFT JOIN `{$catalog_eav_attribute}` ON `{$catalog_eav_attribute}`.`attribute_id` = `{$eav_attributes}`.`attribute_id`
            WHERE `backend_model` IS NOT NULL 
                AND NOT `backend_model` = '' 
                AND `source_model` IS NOT NULL 
                AND NOT `source_model` = ''
                AND (`is_searchable` = 1 OR `is_filterable` = 1)";
        $stm = $this->_read->query($sql);
        return $stm->fetchAll();
    }
    
    protected function export_products($sql, $fileName)
    {
        $this->comments_style('info', "Begining products export", 'info');
        $this->comments_style('info', "Memory usage: " . memory_get_usage(TRUE), 'info');
        
        $this->logProfiler("START export products");
        $this->logProfiler('Mem usage: ' . memory_get_usage(TRUE));
        $startTime = time();
        
        $fields = array('id', 'price', 'image_link', 'thumbnail', 'original_product_image_link', 'type_id', 'sku', 'is_saleable', 'manage_stock', 'is_in_stock', 'qty', 'min_qty');
        
        if ($this->bExportProductLink) {
            $fields[] = 'link';
        }
        
        foreach ($fields as $key => $field) {
            $fields[$key] = Mage::helper('celexport/mapping')->getMapping($field);
        }
        
        //Fetching a list of custom attributes, for which we'll need to map out the values from the corresponding source models.
        $customAttributes = $this->_getCustomAttributes();
        foreach ($customAttributes as $key => $customAttribute) {
            $customAttributes[$key] = $customAttribute['attribute_code'];
            $fields[] = $customAttribute['attribute_code'] . '_value';
        }
        //Creating a custom fields cache for use in the separate processes.
        Mage::getModel('celexport/cache')->setName('export_custom_fields')->setContent(json_encode($customAttributes))->save();
        
        //Dispatching event in case a custom module would want to modify the export process.
        Mage::dispatchEvent('celexport_before_export_products', array(
                'fields'             => &$fields,
                'sql'                => &$sql,
                'filename'           => &$fileName
            ));
        
        //Creating the file handler to save the export results and handling any errors that might occur in the process.
        $fh = $this->create_file($fileName);
        if (!$fh) {
            $this->comments_style('error', 'Could not create the file in ' . $this->_fPath . DIRECTORY_SEPARATOR . $fileName . ' path', 'problem with file');
            $this->logProfiler('Could not create the file in ' . $this->_fPath . DIRECTORY_SEPARATOR . $fileName . ' path');
            return;
        }
        
        //Writing the field names as the header row in the export file.
        $header = "^" . implode("^" . $this->_fDel . "^", $fields) . "^" . "\r\n";
        $this->write_to_file($header, $fh);
        
        // *********************************
        $stm = $this->_read->query($sql . " LIMIT 0, 100000000");
        
        $str='';
        $rows=$stm->fetchAll();
        $chunks = array_chunk($rows, Mage::helper('celexport')->getExportChunkSize());
        $pids = array();
        $finished = array();
        $process_limit = Mage::helper('celexport')->getExportProcessLimit();
        $count = 0;
        
        if (!Mage::getStoreConfig('celexport/advanced/single_process')) {
            //$_fPath = Mage::helper('celexport')->getExportPath($this->_exportProcessId) . '/' . $this->_fStore->getWebsite()->getCode() . '/' . $this->_fStore->getCode();
            /* export with parallel processes */
            foreach ($chunks as $chunk) {
                //Using a random number to identify data chunks in the db and the processes that process them.
                //This is useful in case several exports (for several store views, from different cron jobs)
                // are running in parallel. Had the identifiers been incremental, they'd be the same in each 
                // of these different exports.
                $count += 1;
                $i = $this->_fStore_id * 1000 + $count; //mt_rand();
                if (count($pids) >= $process_limit) {
                    //$counter = 10;
                    do {
                        //$counter--;
                        //if ($counter == 0) break;
                        sleep(1);
                        $state = TRUE;
                        foreach ($pids as $key => $pid) {
                            if (!$this->is_process_running($pid)) {
                                $state = FALSE;
                                $finished[] = $key;
                                unset($pids[$key]);
                            }
                        }
                    } while ($state);
                }
                Mage::getModel('celexport/cache')->setName('export_chunk_' . $i)->setContent(json_encode($chunk))->save();
                $pids[$i] = $this->startProcess($i);
/*$this->logProfiler('Current number of processes is: ' . count($pids) . ' / ' . $i . ' / Mem usage: ' . memory_get_usage(TRUE));*/
                if (!$pids[$i]) {
                    $this->comments_style('error','Could not create a new system process. Please enable shell_exec in php.ini.', 'problem with process');
                    $this->logProfiler('Failed creating a new system process for export parsing.' . ' Process:' . $i);
                    return;
                    //unset($pids[$i]);
                }
            }
            
            do {
                foreach ($pids as $key => $pid) {
                    if (!$this->is_process_running($pid)) {
                        $finished[] = $key;
                        unset($pids[$key]);
                    }
                }
                sleep(1);
            } while (count($pids));
            
            //Mage::log('running time is:');
            //Mage::log(time() - $startTime);
            //Mage::log(date('Y/m/d H:i:s'));
            
            $_fPath = Mage::helper('celexport')->getExportPath($this->_exportProcessId) . '/' . $this->_fStore->getWebsite()->getCode() . '/' . $this->_fStore->getCode();
            if (!is_dir($_fPath)) {
                try {
                    $dir = mkdir($_fPath, 0777, TRUE);
                } catch (Exception $e) {
                    $this->comments_style('error', 'Could not create the directory in ' . $_fPath . ' path', 'problem with dir');
                    $this->logProfiler('Failed creating a directory at: '. $_fPath);
                    return;
                }
            }
            
            foreach ($finished as $key) {
                $item = Mage::getModel('celexport/cache')
                    ->getCollection()
                    ->addFieldToFilter('name', 'process_' . $key)
                    ->getLastItem();
                $status = $item->getContent();
                
                if ($status == 'no_errors') {
                    $filePath = $_fPath . '/' . 'export_chunk_' . $key . "." . 'txt';
                    fwrite($fh, file_get_contents($filePath));
                    unlink($filePath);
                    
                } else {
                    $this->comments_style('error', 'Exception from process: ' . $status, 'problem with process');
                    $this->ftpfile(NULL, FALSE);
                    die;
                }
                
                $item->delete();
            }
            
            fclose($fh);
            
            //Reset the custom fields cache.
            Mage::getModel('celexport/cache')->getCollection()
                ->addFieldToFilter('name', 'export_custom_fields')
                ->getLastItem()
                ->delete();
                
        } else {
        
            /* export without parallel processes */
            $customAttributes = json_decode(Mage::getModel('celexport/cache')
                ->getCollection()
                ->addFieldToFilter('name', 'export_custom_fields')
                ->getLastItem()
                ->getContent());
            $exportHelper = Mage::helper('celexport/export');
            
            foreach ($chunks as $rows) {
                $ids = array();
                foreach ($rows as $row) {
                    $ids[] = $row['entity_id'];
                }
                
                $str = $exportHelper->getProductsData($ids, $customAttributes, $this->_fStore_id);
                fwrite($fh, $str);
            }
            
            fclose($fh);
        }
        
        $this->logProfiler('Mem usage: ' . memory_get_usage(TRUE));
        $this->logProfiler("FINISH export products", NULL, TRUE);
    }
    
    public function startProcess($i)
    {
        $limit = 70;
        $processId = NULL;
        $timeout = 0;
        do {
            if ($timeout) {
                sleep($timeout);
            }
            
            $processId = (int)shell_exec('nohup php ' . Mage::getModuleDir('', 'Celebros_Celexport') . '/Model/processChunkCol.php ' . $i . ' ' . $this->_fStore_id . ' ' . Mage::getBaseDir() . DS . 'shell ' . $this->_exportProcessId . ' > /dev/null & echo $!');
            $timeout += 10; 
        } while ((!$processId) && ($timeout <= $limit));
        
        return $processId;
    }
    
    protected static function startProfiling($key)
    {
        if (!isset(self::$_profilingResults[$key])) {
            $profile = new stdClass();
            $profile->average =0 ;
            $profile->count = 0;
            $profile->max = 0;
            self::$_profilingResults[$key] = $profile;
        }
        $profile = self::$_profilingResults[$key];
        if (isset($profile->start) && $profile->start > $profile->end) {
            throw new Exception("The start of profiling timer '{$key}' is called before the stop of it was called");
        }
        
        $profile->start = (float)array_sum(explode(' ', microtime()));
    }
    
    protected static function stopProfiling($key)
    {
        if (!isset(self::$_profilingResults[$key])) {
            throw new Exception("The stop of profiling timer '{$key}' was called while the start was never declared");
        }
        
        $profile = self::$_profilingResults[$key];
        if ($profile->start == -1) {
            throw new Exception("The start time of '{$key}' profiling is -1");
        }
        
        $profile->end = (float) array_sum(explode(' ', microtime()));
        $duration = $profile->end - $profile->start;
        if ($profile->max < $duration) {
            $profile->max = $duration;
        }
        
        $profile->average = ($profile->average * $profile->count + $duration)/($profile->count +1);
        $profile->count++;
    }
    
    protected static function getProfilingResultsString()
    {
        $html = "";
        if (count(self::$_profilingResults)) {
            $html.= "In sec:";
            $html.=  '<table border="1">';
            $html.=  "<tr><th>Timer</th><th>Total</th><th>Average</th><th>Count</th><th>Peak</th></tr>";
            foreach(self::$_profilingResults as $key =>$profile) {
                $total = $profile->average * $profile->count;
                $html.=  "<tr><td>{$key}</td><td>{$total}</td><td>{$profile->average}</td><td>{$profile->count}</td><td>{$profile->max}</td></tr>";
            }
            $html.=  "</table>";
        }
        
        $html.= 'PHP Memory peak usage: ' . memory_get_peak_usage();
        
        return $html;
    }
    
}