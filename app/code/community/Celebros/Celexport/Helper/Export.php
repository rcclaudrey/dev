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
class Celebros_Celexport_Helper_Export extends Mage_Core_Helper_Abstract
{
    const MIN_MEMORY_LIMIT = 256;
    protected $_storeId;
    
    public function logProfiler($msg, $process = NULL, $isSpaceLine = FALSE)
    {
        if (!Mage::getStoreConfig('celexport/advanced/enable_log')) {
            return;    
        }
        
        $logFileName = $this->getLogFilename($process);
        
        $this->log(date("Y-m-d, H:i:s:: ") . $msg, NULL, $logFileName, TRUE);
        
        if ($isSpaceLine) {
            $this->log('', NULL, $logFileName, TRUE);
        }
    }
    
    public function getLogFilename($processId)
    {
        return $processId . '.log';	
    }
    
    public function getLogFolder()
    {
        return Mage::getBaseDir() . DS . Mage::app()->getStore(0)->getConfig('celexport/export_settings/path') . DS . 'logs';
    }
    
    public function log($message, $level = null, $file = '', $forceLog = false)
    {
        static $loggers = array();
        
        $level  = is_null($level) ? Zend_Log::DEBUG : $level;
        $file = empty($file) ? 'system.log' : $file;
        
        try {
            if (!isset($loggers[$file])) {
                $logDir  = $this->getLogFolder();
                $logFile = $logDir . DS . $file;
                
                if (!is_dir($logDir)) {
                    mkdir($logDir);
                    chmod($logDir, 0777);
                }
                
                if (!file_exists($logFile)) {
                    file_put_contents($logFile, '');
                    chmod($logFile, 0777);
                }
                
                $format = '%timestamp% %priorityName% (%priority%): %message%' . PHP_EOL;
                $formatter = new Zend_Log_Formatter_Simple($format);
                $writerModel = (string)Mage::getConfig()->getNode('global/log/core/writer_model');
                if (!Mage::app() || !$writerModel) {
                    $writer = new Zend_Log_Writer_Stream($logFile);
                }
                else {
                    $writer = new $writerModel($logFile);
                }
                $writer->setFormatter($formatter);
                $loggers[$file] = new Zend_Log($writer);
            }
            
            if (is_array($message) || is_object($message)) {
                $message = print_r($message, true);
            }
            
            $loggers[$file]->log($message, $level);
        } catch (Exception $e) {
        }
    }
    
    public function getProductImage($product, $type)
    {
        Mage::app()->setCurrentStore($this->_storeId);
        //Mage::app()->setCurrentStore(0);
        $bImageExists = 'no_errors';
        $url = NULL;
        try {
            switch ($type) {
                case 'image':
                    $url = (string)Mage::helper('catalog/image')->init($product, 'image', $product->getImage())->resize(250);
                    break;
                case 'thumbnail':  
                    $url = (string)Mage::helper('catalog/image')->init($product, 'thumbnail', $product->getThumbnail())->resize(66);
                    break;  
                case 'original': 
                    $url = (string)Mage::getModel('catalog/product_media_config')->getMediaUrl($product->getImage());
                    break; 
                default:
                    $url = (string)Mage::getModel('catalog/product_media_config')->getMediaUrl($product->getImage());
            }
        } catch (Exception $e) {
            // We get here in case that there is no product image and no placeholder image is set.
            $bImageExists = FALSE;
        }
        
        if (!$bImageExists || (stripos($url, 'no_selection') != FALSE) || (substr($url, -1) == DS)) {
            /*$this->logProfiler('Warning: '. $type . ' Error: Product ID: '. $product->getEntityId() . ', image url: ' . $url, NULL);*/
            return NULL;
        }
     
        return $url;
    }
    
    public function getCalculatedPrice($product)
    {
        Mage::app()->setCurrentStore($this->_storeId);
        $product = Mage::getModel('catalog/product')
            ->setStoreId($this->_storeId)
            ->load($product->getEntityId());
        $price = "";
        
        if ($product->getData("type_id") == "bundle") {
            $priceModel  = $product->getPriceModel();
            $price = $priceModel->getTotalPrices($product, 'min', NULL, FALSE);
        } elseif ($product->getData("type_id") == "grouped") {
            $_taxHelper  = Mage::helper('tax');
            $aProductIds = $product->getTypeInstance()->getChildrenIds($product->getEntityId());
            $prices = array();
            foreach ($aProductIds as $ids) {
                foreach ($ids as $id) {
                    $aProduct = Mage::getModel('catalog/product')->load($id);
                    if ($aProduct->getIsInStock()) {
                        $prices[] =$aProduct->getPriceModel()->getFinalPrice(null, $aProduct, true);
                    }
                }
            }
            asort($prices);
            $price =  array_shift($prices);
        } elseif ($product->getData("type_id") == "giftcard") {
            $min_amount = PHP_INT_MAX;
            $product = Mage::getModel('catalog/product')->load($product->getId());
            if ($product->getData("open_amount_min") != NULL && $product->getData("allow_open_amount")) {
                $min_amount = $product->getData("open_amount_min");
            }
            foreach ($product->getData("giftcard_amounts") as $amount) {
                if ($min_amount > $amount["value"]) {
                    $min_amount = $amount["value"];
                }
            }
            $price =  $min_amount;
        } else {
            $price = $product->getFinalPrice();
        }
       
        return number_format($price, 2, ".", "");
    }
    
    public function getProductsData($ids, $customAttributes, $storeId)
    {
        $this->_storeId = $storeId;
        $str = NULL;
        Mage::app()->setCurrentStore(0);
        $collection = Mage::getModel('catalog/product')->getCollection()
            ->addFieldToFilter('entity_id', array('in' => $ids))
            ->setStoreId($storeId)
            ->addStoreFilter($storeId)
            ->addAttributeToSelect(array('sku', 'price', 'image', 'thumbnail', 'type', 'is_salable'))
            ->addAttributeToSelect($customAttributes)
            ->joinTable(
                Mage::getSingleton('core/resource')->getTableName('cataloginventory_stock_item'),
                'product_id=entity_id',
                array('manage_stock', 'is_in_stock', 'qty', 'min_sale_qty'),
                NULL,
                'left'
            );
        
        foreach ($collection as $product) {
            $values = array(
                "id"                          => $product->getEntityId(),
                "price"                       => Mage::helper('core')->currency($this->getCalculatedPrice($product), FALSE, FALSE),
                "image_link"                  => $this->getProductImage($product, 'image'),
                "thumbnail"                   => $this->getProductImage($product, 'thumbnail'),
                "original_product_image_link" => $this->getProductImage($product, 'original'),
                "type_id"                     => $product->getTypeId(),
                "product_sku"                 => $product->getSku(),
                "manage_stock"                => $product->getManageStock() ? "1" : "0",
                "is_salable"                  => ($product->getIsSalable() == '1') ? "1" : "0",
                "is_in_stock"                 => $product->getIsInStock() ? "1" : "0",
                "qty"                         => (int)$product->getQty(),
                "min_qty"                     => (int)$product->getSaleMinQty(),
                "link"                        => $product->getProductUrl()
            );
            
            //Process custom attributes.
            foreach ($customAttributes as $customAttribute) {
                $values[$customAttribute] = ($product->getData($customAttribute) == "") ? "" : trim($product->getResource()->getAttribute($customAttribute)->getFrontend()->getValue($product), " , ");
            }
            
            //Dispatching an event so that custom modules would be able to extend the functionality of the export,
            // by adding their own fields to the products export file.
            Mage::dispatchEvent('celexport_product_export', array(
                'values'  => &$values,
                'product' => &$product,
            ));
            
            $fDel = Mage::getStoreConfig('celexport/export_settings/delimiter');
            if ($fDel === '\t') $fDel = chr(9);
            
            $str.= "^" . implode("^" . $fDel . "^", $values) . "^" . "\r\n";
            
            $product->clearInstance();
            $product->reset();
        }
        return $str;
    }
    
    public function getMemoryLimit()
    {
        $limit = (int) Mage::getStoreConfig('celexport/advanced/memory_limit');
        if (!$limit
        || $limit < self::MIN_MEMORY_LIMIT) {
            return self::MIN_MEMORY_LIMIT;
        }
        
        return $limit;
    }
    
}