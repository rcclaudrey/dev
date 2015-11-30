<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Sorting
 */
class Amasty_Sorting_Model_Method_Qty extends Amasty_Sorting_Model_Method_Abstract
{
    
    public function getCode()
    {
        return 'qty';
    }    
    
    public function getName()
    {
        return 'Quantity';
    }    
    
    // it is an internal method, we don't need name, code, ect for it
    public function apply($collection, $currDir)  
    {

        /**
         * @var Varien_Db_Select $select
         */
        $select = $collection->getSelect();
        if (strpos($select, 'cataloginventory_stock_status')){
            $select->reset(Zend_Db_Select::ORDER);

        } else {
            Mage::getResourceModel('cataloginventory/stock_status')
                ->addStockStatusToSelect($select, Mage::app()->getWebsite());
        }

        $this->_doWarehouseCompatibility($select);

        $select->order('stock_status.qty ' . $currDir);
               
        return $this;
    }

    /**
     * @param Varien_Db_Select $select
     */
    protected function _doWarehouseCompatibility($select)
    {
        if (Mage::getConfig()->getModuleConfig('Innoexts_Warehouse')->is('active', 'true')) {
            $storeId = Mage::app()->getStore()->getId();
            $stockId = Mage::helper('warehouse')->getStockIdByStoreId($storeId)
                ? Mage::helper('warehouse')->getStockIdByStoreId($storeId)
                : Mage::helper('warehouse')->getDefaultStockId();

            $select->where('stock_status.stock_id = ?', $stockId);
        }
    }
}