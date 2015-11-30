<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Rma
 */ 
class Amasty_Rma_Model_Item extends Mage_Core_Model_Abstract
{
    protected $_aviableProductTypes = array(
        Mage_Catalog_Model_Product_Type::TYPE_SIMPLE,
        Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE,
        Mage_Catalog_Model_Product_Type::TYPE_GROUPED,
        Mage_Catalog_Model_Product_Type::TYPE_BUNDLE
    );
    
    public function _construct()
    {
        $this->_init('amrma/item');
    }
    
    public function getSalesOrderItemCollection(){
        $hlr = Mage::helper("amrma");
        $minAlowedDays = $hlr->getMinAllowedDays();
        $maxAlowedDays = $hlr->getMaxAllowedDays();
        
        $collection = Mage::getModel('sales/order_item')
            ->getCollection();
        
        $collection
            ->addFieldToFilter('product_type', array("in" => $this->_aviableProductTypes));
//            ->addFieldToFilter('qty_shipped', array("gt" => 0));
        
        if (!empty($minAlowedDays)){
            $date = Mage::getSingleton('core/date');
            
            $t = ($date->timestamp() - (60*60*24*$minAlowedDays));
            
            $collection->addFieldToFilter('main_table.created_at', array(
                'lt' => $date->gmtDate('Y-m-d H:i:s', $t),
            ));
        }
        
        if (!empty($maxAlowedDays)){
            $date = Mage::getSingleton('core/date');
            
            $t = ($date->timestamp() - (60*60*24*$maxAlowedDays));
            
            $collection->addFieldToFilter('main_table.created_at', array(
                'gt' => $date->gmtDate('Y-m-d H:i:s', $t),
            ));
        }
        
        return $collection;
    }
    
    public function getOrderItemsCollection($orderId)
    {
        $collection = $this->getSalesOrderItemCollection();
        $collection->addFieldToFilter('order_id', $orderId);
        return $collection;
    }
    
    public function getOrderItems($orderId){
        $orderItemsCollection = $this->getOrderItemsCollection($orderId);
        
        foreach($orderItemsCollection as $item){
            $item->setName($this->getProductName($item));
            $item->setAvailableQty($item->getQtyOrdered()-$item->getQtyRefunded()-$item->getQtyCanceled());
//            $item->setAvailableQty($item->getQtyShipped()-$item->getQtyRefunded()-$item->getQtyCanceled());
        }
        return $orderItemsCollection;
    }
    
    public function getProductName($item)
    {
        $name   = $item->getName();
        $result = array();
        if ($options = $item->getProductOptions()) {
            if (isset($options['options'])) {
                $result = array_merge($result, $options['options']);
            }
            if (isset($options['additional_options'])) {
                $result = array_merge($result, $options['additional_options']);
            }
            if (isset($options['attributes_info'])) {
                $result = array_merge($result, $options['attributes_info']);
            }

            if (!empty($result)) {
                $implode = array();
                foreach ($result as $val) {
                    $implode[] =  isset($val['print_value']) ? $val['print_value'] : $val['value'];
                }
                return $name.' ('.implode(', ', $implode).')';
            }
        }
        return $name;
    }
}
?>