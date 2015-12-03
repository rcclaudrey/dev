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
set_time_limit(60000);
ini_set('max_execution_time', 60000);
ini_set('display_errors', 1);
ini_set('output_buffering', 0);

class Celebros_Celexport_Model_Exporter
{
    
    protected $_errors = array();
    protected $_config;
    protected $_conn;
    protected $_read;
    protected $_fDel;
    protected $_fEnclose;
    protected $_fPath;
    protected $_fType;
    protected $_fStore_id;
    protected $_fStore;
    protected $_fStore_module_enabled;
    protected $_fProducts_Collection;
    protected $_fProduct_Category_Matrix;
    protected $_fSize;
    protected $_updateStock;
    protected $_flushRecordsCount = 500;
    protected $_fileNameTxt = "products.txt";
    protected $_fileNameZip;
    protected $_getChildrenOfGroupProducts = TRUE;
    protected $_bUpload = TRUE;
    protected $_aProductPricingTiers;
    protected $isWebRun = FALSE;
    protected $isLogProfiler=TRUE;
    protected $_storeSpecificRun = FALSE;
    protected $_store_export_status = NULL;
    protected $_exportProcessId = NULL;
    
    public function __construct()
    {
    }
    
    protected function logProfiler($msg, $process = NULL, $isSpaceLine = FALSE)
    {
        if (!$process) {
            $process = $this->_exportProcessId;
        }
        
        Mage::helper('celexport/export')->logProfiler($msg, $process, $isSpaceLine);
    }
    
    
    /**
     * Retrieve celexport session
     *
     * @return Mage_Catalog_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('celexport/session');
    }
    
    /**
     * Daily update catalog to celexport server by cron
     * This method is called from cron process, cron is workink in UTC time and
     *
     * @param   Varien_Event_Observer $observer
     * @return  Celebros_Conversionpro_Model_Observer
     */
    public function catalogUpdate($observer)
    {
        //This data is saved to the registry so that it would persist when isStoreExportEnabled()
        // is called from within another class (observerLarge).
        Mage::helper('celexport')->setCronJobCode($observer->getData('job_code'));
        $this->export_celebros(FALSE);
        
        return $this;
    }
    
    /**
     * Update stock after product update in the backend
     * 
     * @param Varien_Event_Observer $observer
     */
    public function updateStockConfig($observer)
    {
        if($this->_updateStock==1)
        {
        $event = $observer->getEvent();
        $product = $event->getProduct();
        $Stock =$product->getData('stock_data');
        if(isset($Stock['is_in_stock']))
            $isInStock=$Stock['is_in_stock'];
        else
            $isInStock=0;
        $sku = $product->getSku();
        if ((int)$isInStock == 0){
            Mage::helper('celexport')->getSalespersonApi()->RemoveProductFromStock($sku);
        }
        else {
            Mage::helper('celexport')->getSalespersonApi()->RestoreProductToStock($sku);
        }
        }
    }
    
    /**
     * Update stock after order checkout process in the front-end
     * 
     * @param Varien_Event_Observer $observer $observer
     */
    public function updateStockOrder($observer)
    {
        if($this->_updateStock==1)
        {
        $event = $observer->getEvent();
        $order = $event->getOrder();
        $productModel = Mage::getSingleton('catalog/product');
        $itemModel = Mage::getSingleton('cataloginventory/stock_item');
        foreach ($order->getAllItems() as $item){
            $product_info = $item->getProductOptions();
            $product_id = $product_info['info_buyRequest']['product'];
            $product = $productModel->load($product_id);
            $inventoery = $itemModel->loadByProduct($product);
            $isInStock = $inventoery->getData('is_in_stock');
            $sku = $product->getSku();
            if ((int)$isInStock == 0){
                Mage::helper('celexport')->getSalespersonApi()->RemoveProductFromStock($sku);
            }
            else {
                Mage::helper('celexport')->getSalespersonApi()->RestoreProductToStock($sku);
            }
        }
        }
    }
    
    /**
     * Recursive function that cleans the contents of a folder, from both files and subfolders.
     * Partially taken from PHP's rmdir documentation comments.
     */
    public function delTree($dir)
    {
        //If the passed variable is a file and not a folder, just delete it.
        if (is_file($dir)) {
            unlink($dir);
            return;
        }
        
        //Get list of file and folder paths, with folders prefixed by a slash.
        $files = glob($dir . '/*', GLOB_MARK);
        foreach ($files as $file) {
            if (substr($file, -1) == '/') {
                //Path is a folder, run recursively on it.
                $this->delTree($file);
            } else {
                //Path is a file. Delete it.
                unlink($file);
            }
        }
        //In case the directory is empty, remove it.
        rmdir($dir);
    }
    
    /**
     * Checks whether export should run on a specific store view.
     * This includes both the export_enabled field, 
     *  as well as checking if cron is set to run for this store view in the current iteration.
     */
    public function isStoreExportEnabled($store_id)
    {
        $helper = Mage::helper('celexport');
        //$store_export_status = $helper->setStoreExportStatus();
        if (!isset($this->_store_export_status)) {
            $this->_store_export_status = array();
            
            if (!$this->isWebRun) {
                $job_code = $helper->getCronJobCode();
                $store_view = substr($job_code, strripos($job_code, '_', -1) + 1);
            }
            
            foreach (Mage::app()->getStores() as $store) {
                $this->_store_export_status[$store->getStoreId()] = FALSE;
                //Check whether export is enabled for this store view.
                if ($store->getConfig('celexport/export_settings/export_enabled')) {
                    //When running from cron, we need to make sure it's enabled and set to run on this store view.
                    if (!$this->isWebRun) {
                        
                        //If we've manually scheduled a cron job, it'll have no number suffix, thus 
                        // the result of the strripos from before would be the 'e' from 'celexport_export'.
                        if ($store_view == 'export') {
                            $this->_store_export_status[$store->getStoreId()] = TRUE;
                            continue;
                        }
                        
                        //This checks whether cron is enabled for this store view.
                        $cron_enabled = Mage::getStoreConfigFlag('celexport/export_settings/cron_enabled', $store->getStoreId());
                        if ($cron_enabled) {
                            
                            //This checks for whether the current execution time matches the 
                            // cron expression for this store view.
                            //$cronModel = new Mage_Cron_Model_Schedule;
                            
                            
                            if ((int)$store_view == $store->getStoreId()) {
                                $this->_store_export_status[$store->getStoreId()] = TRUE;
                            } else if ((int)$store_view == 0) {
                                $expected = 'crontab/jobs/celexport_export_' . $store->getStoreId() . '/schedule/cron_expr';
                                
                                $item = Mage::getModel('core/config_data')
                                    ->getCollection()
                                    ->addFieldToFilter('path', $expected)
                                    ->getFirstItem();
                                    
                                if (!count($item->getData())) {
                                    $this->_store_export_status[$store->getStoreId()] = TRUE;
                                }
                            }
                            /*
                            $expr = Mage::getStoreConfig('celexport/export_settings/cron_expr', $store->getStoreId());
                            $isCronRunningOnStore = FALSE;
                            if (isset($expr) && $expr != '') {
                                $isCronRunningOnStore = $cronModel->setCronExpr($expr)
                                                            ->trySchedule($helper->getCronExecutionTime());
                            }
                            
                            if ($isCronRunningOnStore) {
                                $store_export_status[$store->getStoreId()] = TRUE;
                            }
                            */
                        }
                    } else {
                        //if export is enabled and we're not running from cron, it's always TRUE.
                        $this->_store_export_status[$store->getStoreId()] = TRUE;
                    }
                }
            }
            //This data is saved to the registry, because this function is called 
            // again from another class (observerLarge), and that's the only way to 
            // persist the array's data across different classes.
            $helper->setStoreExportStatus($this->_store_export_status);
        }
        
        return $this->_store_export_status[$store_id];
    }
    
    /**
     * Removes the contents of the export folder without removing the root export folder itself.
     */
    public function cleanExportDirectory()
    {
        $dir = getcwd() . Mage::app()->getStore(0)->getConfig('celexport/export_settings/path');
        if (is_dir($dir)) {
            $files = scandir($dir);
            foreach ($files as $file) {
                if (filectime($dir . DS . $file) < strtotime('-' . Mage::app()->getStore(0)->getConfig('celexport/export_settings/export_lifetime', 1) . ' day')
                && !in_array($file, array('.', '..'))) {
                    shell_exec('rm -rf ' . $dir . DS . $file);
                }
            }
        }
    }
    
    public function export_celebros($webAdmin)
    {
        $this->isWebRun = $webAdmin;
        $this->_exportProcessId = Mage::helper('celexport')->getExportProcessId();
        
        $export_start = (float)array_sum(explode(' ',microtime()));
        $this->comments_style('header',0,0);
        $this->comments_style('icon', date('Y/m/d H:i:s').', Starting profile execution, please wait...', 'icon');
        $this->comments_style('icon', 'Memory Limit: ' . ini_get('memory_limit'), 'icon');
        $this->comments_style('warning', 'Warning: Please don\'t close window during importing/exporting data', 'warning');
        
        //Remove the contents of the export folder in case a previous process got stuck and the files weren't deleted.
        $this->cleanExportDirectory();
        
        //Running the orders export without any verifications of the config values, as we'll need to iterate over
        // store specific values and check whether this is enabled or not for each store.
        $this->export_orders_celebros();
        
        //Connect to the database
        $this->_read=Mage::getSingleton('core/resource')->getConnection('core_read');
        
        $model = Mage::getModel('celexport/ObserverLarge');
        $model->export_celebros($webAdmin, $this->_exportProcessId);
        $export_end = (float)array_sum(explode(' ', microtime()));
        
        $this->comments_style('info','Finished profile execution within ' . round($export_end - $export_start, 3) . ' sec.','finish');
        $this->comments_style('finish', 0, 0);
    }
    
    public function getTierPriceString()
    {
        $tier_price = "";
        if (is_array($this->_aProductPricingTiers) && count($this->_aProductPricingTiers)) {
            $arr = array();
            foreach ($this->_aProductPricingTiers as $tier) {
                $price_qty = $tier["price_qty"];
                $website_price = $tier["website_price"];
                $arr[$price_qty] = $website_price;
            }
            
            $tier_price = Mage::helper('celexport')->array_implode("=>", ",", $arr);
        }
        
        
        return $tier_price;
    }
    
    public function ftpfile($zipFilePath, $zipUpload = TRUE)
    {
        if (!file_exists($zipFilePath)) {
            $this->comments_style('error','No ' . $zipFilePath . ' file found','No_zip_file_found');
            
            return FALSE;
        }   
        
        $ioConfig=array();
        
        if ($this->_fFTPHost != '') {
            $ioConfig['host'] = $this->_fFTPHost;
        } else {
            $this->comments_style('error','Empty host specified','Empty_host');
            return FALSE;
        }
        
        if ($this->_fFTPPort != '') {
            $ioConfig['port'] = $this->_fFTPPort;
        }
        
        if ($this->_fFTPUser != '') {
            $ioConfig['user'] = $this->_fFTPUser;
        } else {
            $ioConfig['user']='anonymous';
            $ioConfig['password']='anonymous@noserver.com';
        }
        
        if ($this->_fFTPPassword != '') {
            $ioConfig['password'] = $this->_fFTPPassword;
        }
        
        $ioConfig['passive'] = $this->_fFTPPassive;
        
        if ($this->_fPath != '') {
            $ioConfig['path']= $this->_fPath;
        }
        $this->_config = $ioConfig;
        $this->_conn =@ftp_connect($this->_config['host'], $this->_config['port']);
        
        if (!$this->_conn) {
            $this->comments_style('error','Could not establish FTP connection, invalid host or port','invalid_ftp_host/port');
            
            return FALSE;
        }
        if (!@ftp_login($this->_conn, $this->_config['user'], $this->_config['password'])) {
            $this->close();
            $this->comments_style('error','Could not establish FTP connection, invalid user name or password','Invalid_ftp_user_name_or_password');
            
            return FALSE;
        }
        
        if (!@ftp_pasv($this->_conn, TRUE)) {
            $this->close();
            $this->comments_style('error','Invalid file transfer mode','Invalid_file_transfer_mode');
            
            return FALSE;
        }
        
        if ($zipUpload) {
            if (!file_exists($zipFilePath)) {
                $this->comments_style('error','No ' . $zipFilePath . ' file found','No_zip_file_found');
            }       
            
            $upload = @ftp_put($this->_conn, basename($zipFilePath), $zipFilePath, FTP_BINARY);
            
            if (!$upload) {
                 $this->comments_style('error','File upload failed','File_upload_failed');
                 $upload=FALSE;
            }
        }
        
        $this->uploadLog($this->_conn);
        
        return $upload;
    }
    
    public function uploadLog($connection)
    {
        $helper = Mage::helper('celexport/export');
        $logfilename = $helper->getLogFilename($this->_exportProcessId);
        @ftp_put($connection, 'celebros.log', $helper->getLogFolder() . DS . $logfilename, FTP_BINARY);  
    }
    
    public function close()
    {
        return ftp_close($this->_conn);
    }
    
    public function comments_style($kind,$text,$alt)
    {
        if (!$this->isWebRun)
            return;
        switch($kind)
        {
        case 'header':
        echo    '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
            <html><head><style type="text/css">
            ul { list-style-type:none; padding:0; margin:0; }
            li { margin-left:0; border:1px solid #ccc; margin:2px; padding:2px 2px 2px 2px; font:normal 12px sans-serif;  }
            img { margin-right:5px; }
            </style><title>Conversion Pro Exporter</title></head>
            <body><ul>';
        break;
        case 'icon':
        echo    '<li style="background-color: rgb(128, 128, 128); color:rgb(255,255,255);">
            <img style="margin-right: 5px;" src="'.Mage::getBaseUrl().'../skin/adminhtml/default/default/images/note_msg_icon.gif" alt='.$alt.'/>
            '.$text.'
            </li>';
        break;
        case 'info':
            echo    '<li>
            <img style="margin-right: 5px;" src="'.Mage::getBaseUrl().'../skin/adminhtml/default/default/images/note_msg_icon.gif" alt='.$alt.'/>
            '.$text.'
            </li>';
            break;
            case 'warning':
        echo    '<li style="background-color: rgb(255, 255, 128);">
            <img style="margin-right: 5px;" src="'.Mage::getBaseUrl().'../skin/adminhtml/default/default/images/fam_bullet_error.gif" alt='.$alt.'/>
            '.$text.'
            </li>';
        break;
        case 'success':
        echo '<li style="background-color: rgb(128, 255, 128);">
            <img src="'.Mage::getBaseUrl().'../skin/adminhtml/default/default/images/fam_bullet_success.gif" alt='.$alt.'/>
            '.$text.'
            </li>';
        break;
        case 'section':
        echo '<li style="background-color: rgb(100, 149, 237);">
            <img src="'.Mage::getBaseUrl().'../skin/adminhtml/default/default/images/fam_bullet_success.gif" alt='.$alt.'/>
            '.$text.'
            </li>';
        break;
        case 'error':
        echo '<li style="background-color: rgb(255, 187, 187);">
            <img src="'.Mage::getBaseUrl().'../skin/adminhtml/default/default/images/error_msg_icon.gif" alt='.$alt.'/>
            '.$text.'
            </li>';
        break;
        default:
        echo '</ul></body></html>';
        }
    }
    
    public function export_config($store_id)
    {
        $this->_fStore_id = $store_id;
        /*Mage::app()->setCurrentStore($store_id);
        $this->_fStore = Mage::app()->getStore($store_id);*/
        $this->_fStore = Mage::getModel('core/store')->load($store_id);
        $this->_fStore_export_enabled = $this->_fStore->getConfigFlag('celexport/export_settings/export_enabled');
        //feature is not in use
        if (isset($_GET["upload"]) && $_GET["upload"] == "false") {
            $this->_bUpload = FALSE;
        } elseif (getenv("upload") && getenv("upload") == "false") {
            $this->_bUpload = FALSE;
        } else {
            $this->_bUpload = TRUE;
        }
        //end
        $this->_fDel = $this->_fStore->getConfig('celexport/export_settings/delimiter');
        if ($this->_fDel === '\t') {
            $this->_fDel = chr(9);
        }
        
        $this->_fEnclose = $this->_fStore->getConfig('celexport/export_settings/enclosed_values');
        $this->_fType = $this->_fStore->getConfig('celexport/export_settings/type');
        $this->_fPath = Mage::helper('celexport')->getExportPath($this->_exportProcessId) . '/' . $this->_fStore->getWebsite()->getCode() . '/' . $this->_fStore->getCode();
        $this->_fFTPHost = $this->_fStore->getConfig('celexport/export_settings/ftp_host');
        $this->_fFTPPort = $this->_fStore->getConfig('celexport/export_settings/ftp_port');
        $this->_fFTPUser = $this->_fStore->getConfig('celexport/export_settings/ftp_user');
        $this->_fFTPPassword = $this->_fStore->getConfig('celexport/export_settings/ftp_password');
        $this->_fFTPPassive = $this->_fStore->getConfig('celexport/export_settings/passive');
        //feature is not in use
        $this->_fEnableCron = $this->_fStore->getConfigFlag('celexport/export_settings/cron_enabled');
        $this->CronExpression = $this->_fStore->getConfig('celexport/export_settings/cron_expr');
        //end
    }
    
    public function getTableName($tableName)
    {
        $newTableName= Mage::getSingleton('core/resource')->getTableName($tableName);
        return $newTableName;
    }
    
    public function getUrl(Varien_Event_Observer $observer)
    {
        $category = $observer->getCategory();
        $url = Mage::helper('celexport')->getResultUrl($category->getName());
        $category->setData('url', $url);
    }
    
    public function export_orders_celebros()
    {
        $enclosed = '"';
        $delimeter = "  ";
        $newLine = "\r\n";
        
        //We'll run the orders export for each store where crosssell is enabled.
        $this->_read=Mage::getSingleton('core/resource')->getConnection('core_read');
        $store_view_ids = $this->_read->fetchAll('SELECT store_id FROM '.$this->getTableName("core_store") . ' WHERE store_id <> 0'); //Added for multi store view purposes by Eli Sagy
        
        foreach($store_view_ids as $store_view_id)
        {
            $this->export_config($store_view_id['store_id']);
            
            if (!$this->isStoreExportEnabled($store_view_id['store_id']) 
            || !Mage::getStoreConfigFlag('celexport/export_settings/export_data_history')) {
                continue;
            }
                
            $header = array("OrderID", "ProductID", "Date", "Count", "Sum");
            $glue = $enclosed . $delimeter . $enclosed;
            $strResult = $enclosed . implode($glue, $header) . $enclosed . $newLine;
            
            $sql = $this->_getOrdersSql();
            $stm = $this->_read->query($sql);
            
            while ($row = $stm->fetch()) {
                $record["OrderID"] = $row["order_id"];
                $record["ProductID"] = $row["product_id"];
                $created_at_time = strtotime($row["created_at"]);
                $record["Date"] = date("Y-m-d", $created_at_time);
                $record["Count"] = (int)$row["qty_ordered"];
                $record["Sum"] = $row["row_total"];;
                $strResult .= $enclosed . implode($glue, $record) . $enclosed . $newLine;
            }
            
            //Create, flush, zip and ftp the orders file
            $zipFileName = Mage::getStoreConfig('celexport/export_settings/datahistoryname', $store_view_id["store_id"]);
            
            $this->_createAndUploadOrders($zipFileName,$strResult);
            
            $this->logProfiler("Exported orders of store  {$this->_fStore_id} to file {$zipFileName}. Memory peak was: " . memory_get_peak_usage());
            $this->comments_style('success', "Exported orders of store  '{$this->_fStore_id} to file {$zipFileName}'. Memory peak was: " . memory_get_peak_usage(), 'orders');
        }
    }
    
    protected function _getOrdersSql()
    {
        $sql = "";
        $from = date("Y-m-d H:i:s", (time() - 60 * 60 * 24 * 30 * 6)); //return approximately last 6 months orders
        $to = date("Y-m-d H:i:s", time());
        $orderItemTable = $this->getTableName("sales_flat_order_item");
        
        if ($this->_isStoreIdColumnExist($orderItemTable)) {
            $sql = "SELECT order_id, product_id, created_at, qty_ordered, row_total, row_total_incl_tax
            FROM {$orderItemTable}
            WHERE store_id = {$this->_fStore_id} AND created_at between '{$from}' AND '{$to}' AND parent_item_id IS NULL
            ";
        } else {
            $ordersTable = $this->_getOrdersTable();
            $sql = "SELECT item.order_id, item.product_id, item.created_at, item.qty_ordered, item.row_total
            FROM
                (SELECT order_id, product_id, created_at, qty_ordered, row_total
                FROM {$orderItemTable}
                WHERE created_at between '{$from}' AND '{$to}' AND parent_item_id IS NULL) as item
            JOIN
                (SELECT entity_id
                FROM {$ordersTable}
                WHERE store_id = {$this->_fStore_id} AND created_at between '{$from}' AND '{$to}') as sales_order
            ON sales_order.entity_id = item.order_id
            ";
        }
        
        return $sql;
    }
    
    protected function _isStoreIdColumnExist($table)
    {
        $sql = "SHOW COLUMNS FROM {$table} LIKE 'store_id'";
        
        return (bool)$this->_read->fetchOne($sql);
    }
    
    protected function _getOrdersTable()
    {
        $table = $this->getTableName("sales_flat_order");
        $sql = "SHOW TABLES LIKE '{$table}'";
        $bExist = (bool)$this->_read->fetchOne($sql);
        
        return ($bExist) ? $table : $this->getTableName("sales_order");
    }
    
    protected function _createAndUploadOrders($zipFileName, $str)
    {
        //Create directory to put the file
        if (!$this->_createDir($this->_fPath)) {
            $this->comments_style('error', 'Could not create the directory in ' . $this->_fPath . ' path', 'problemwith dir');
            
            return;
        }
        
        $filePath = $this->_fPath . DIRECTORY_SEPARATOR . "Data_History.txt";
        $zipFilePath = $this->_fPath . DIRECTORY_SEPARATOR . $zipFileName;
        
        //Create file
        if ((!$fh = $this->_createFile($filePath))) {
            $this->comments_style('error', 'Could not create the file in ' . $filePath, 'problemwith file');
            
            return;
        }
        
        //Flush string orders data to file
        $this->_stringToTextFile($str, $fh);
        fclose($fh);
        
        //Zip file
        $this->_zipFile($filePath, $zipFilePath);
        
        //Ftp file
        if($this->_fType==="ftp" && $this->_bUpload)
        {
            $ftpRes = $this->ftpfile($zipFilePath);
            if (!$ftpRes) {
                $this->comments_style('error', 'Could not upload ' . $zipFilePath . ' to ftp', 'Could_not_upload_to_ftp');
            }
        }
    }
    
    protected function _createDir($dirPath)
    {
        if (!is_dir($dirPath)) {
            $dir=@mkdir($dirPath, 0777,TRUE);
        }
        
        return $dirPath;
        
    }
    
    protected function _createFile($filePath)
    {
    if (file_exists($filePath)) {
        unlink($filePath);
        }
        
        $fh = fopen($filePath, 'ab');
        return $fh ;
        
    }
    
    protected function _stringToTextFile($str, $fh)
    {
        fwrite($fh, $str);
    }
    
    protected function _zipFile($filePath, $zipFilePath)
    {
        $out = FALSE;
        if (!file_exists($filePath)) {
            $this->comments_style('error', 'No ' . $filePath . ' file found', 'No_txt_file_found');
            exit();
            
            return FALSE;
        }
        
        $zip = new ZipArchive();
        if ($zip->open($zipFilePath, ZipArchive::CREATE) == TRUE) {
            $out = $zip->addFile($filePath, basename($filePath));
            if (!$out) {
                $this->comments_style('error', 'Could not add ' . $filePath . 'to zip archive', 'Could_not_add_txt_file_to_zip_file');
            }
            
            $zip->close();
            unlink($filePath);
        } else {
            $this->comments_style('error', 'Could not create ' . $zipFilePath . ' file', 'Could_not_create_zip_file');
        }
        
        return $out;
    }
    
}