<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Sorting
 */
class Amasty_Sorting_Model_Method_Instock extends Amasty_Sorting_Model_Method_Abstract
{
    // it is an internal method, we don't need name, code, ect for it
    public function apply($collection, $currDir)  
    {
        $show = Mage::getStoreConfig('amsorting/general/out_of_stock_last');
        if (!$show)
            return $this;
            
        //skip search results    
        $isSearch = in_array(Mage::app()->getRequest()->getModuleName(), array('sqli_singlesearchresult', 'catalogsearch')); 
        if ($isSearch && 2 == $show)
            return $this;
        
        $select = $collection->getSelect();
		
        if (!strpos($select->__toString(), 'cataloginventory_stock_status')) {
            $website = Mage::app()->getWebsite();
            if (Mage::helper('core')->isModuleEnabled('Wyomind_Advancedinventory')) {
                $select->joinLeft(
                    array('stock_status' => Mage::getSingleton('core/resource')->getTableName('cataloginventory_stock_status')),
                    'e.entity_id = stock_status.product_id AND stock_status.stock_id=1 AND stock_status.website_id=' . $website->getId(),
                    array('salable' => 'stock_status.stock_status')
                );
            } elseif (Mage::helper('core')->isModuleEnabled('Multiple_CatalogInventory')) {
                $select->joinLeft(
                    array('stock_store' => Mage::getSingleton('core/resource')->getTableName('cataloginventory_stock')),
                    'stock_store.store_id = ' . Mage::app()->getStore()->getId(),
                    array()
                );
                $select->joinLeft(
                    array('stock_status' => Mage::getSingleton('core/resource')->getTableName('cataloginventory_stock_status')),
                    'e.entity_id = stock_status.product_id AND stock_status.stock_id = stock_store.stock_id AND stock_status.website_id=' . $website->getId(),
                    array('salable' => 'stock_status.stock_status')
                );
            } else {
                Mage::getResourceModel('cataloginventory/stock_status')->addStockStatusToSelect($select, $website);
            }
        }
        
        $field = 'salable desc';
        if (Mage::getStoreConfig('amsorting/general/out_of_stock_qty')){
            $field = new Zend_Db_Expr('IF(stock_status.qty > 0, 0, 1)');
        }
        $select->order($field);
        
        // move to the first position
        $orders = $select->getPart(Zend_Db_Select::ORDER);
        if (count($orders) > 1){
            $last = array_pop($orders);
            array_unshift($orders, $last);
            $select->setPart(Zend_Db_Select::ORDER, $orders); 
        }            
               
        return $this;
    }
}