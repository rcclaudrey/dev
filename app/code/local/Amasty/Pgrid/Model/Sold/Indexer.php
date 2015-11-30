<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Pgrid
 */
class Amasty_Pgrid_Model_Sold_Indexer extends Mage_Index_Model_Indexer_Abstract
{
    /**
     * Retrieve Indexer name
     *
     * @return string
     */
    public function getName()
    {
        return Mage::helper('ampgrid')->__('Qty Sold');
    }

    /**
     * Retrieve Indexer description
     *
     * @return string
     */
    public function getDescription()
    {
        return Mage::helper('ampgrid')->__('Qty Sold');
    }

    /**
     * Register data required by process in event object
     *
     * @param Mage_Index_Model_Event $event
     */
    protected function _registerEvent(Mage_Index_Model_Event $event)
    {

    }

    protected function _registerCatalogInventoryStockItemEvent(Mage_Index_Model_Event $event)
    {

    }

    public function matchEvent(Mage_Index_Model_Event $event)
    {
        return false;
    }

    public function reindexQtySold($productIds)
    {
        if (!is_array($productIds)) {
            $productIds = array($productIds);
        }
        $productIdsInt = array_map('intval',$productIds);
        $productIds = implode(',', $productIdsInt);
        $resource = Mage::getSingleton('core/resource');
        $connection = $resource->getConnection('core_write');
        $orderTable = $resource->getTableName('sales_flat_order_item');
        $qtySoldTable = $resource->getTableName('am_pgrid_qty_sold');
        $addToSelect = $this->getDateSold();
        $connection->query("delete from $qtySoldTable where product_id in ($productIds)");
        $connection->query("insert into $qtySoldTable (product_id,  qty_sold)
            select o.product_id, sum(o.qty_ordered)-sum(o.qty_refunded) as qty_sold from $orderTable as o where o.product_id in ($productIds) $addToSelect group by o.product_id");

        return $this;
    }

    public function revertProductsSale($orderData)
    {
        $resource = Mage::getSingleton('core/resource');
        $connection = $resource->getConnection('core_write');
        $qtySoldTable = $resource->getTableName('am_pgrid_qty_sold');
        $orderTable = $resource->getTableName('sales_flat_order_item');
        foreach ($orderData as $productId => $data) {
            $orderItemId = $data['order_item_id'];
            $addToSelect = $this->getDateSold();
            $itemId = $connection->fetchOne("select item_id from $orderTable where item_id=$orderItemId $addToSelect");
            if ($itemId) {
                $qty = $data['qty'];
                $connection->query("update $qtySoldTable set qty_sold = qty_sold - $qty where product_id=$productId");
            }
        }

        return $this;
    }

    /**
     * Process event
     *
     * @param Mage_Index_Model_Event $event
     */
    protected function _processEvent(Mage_Index_Model_Event $event)
    {

    }

    public function reindexAll()
    {
        $productIds = Mage::getModel('catalog/product')->getCollection()->getAllIds();
        if (!empty($productIds)) {
            $productIds = implode(',', $productIds);
            $resource = Mage::getSingleton('core/resource');
            $connection = $resource->getConnection('core_write');
            $orderTable = $resource->getTableName('sales_flat_order_item');
            $qtySoldTable = $resource->getTableName('am_pgrid_qty_sold');
            $addToSelect = $this->getDateSold();
            $connection->query("delete from $qtySoldTable");
            $connection->query("insert into $qtySoldTable (product_id,  qty_sold)
            select o.product_id, sum(o.qty_ordered)-sum(o.qty_refunded) as qty_sold from $orderTable as o where o.product_id in ($productIds) $addToSelect group by o.product_id");
        }
    }

    protected function getDateSold ()
    {
        $dateFrom = Mage::getStoreConfig('ampgrid/additional/qty_sold_from');
        $dateTo = Mage::getStoreConfig('ampgrid/additional/qty_sold_to');
        $addToSelect = '';
        if ($dateFrom && $dateTo) {
            $addToSelect = " and created_at BETWEEN '$dateFrom' AND '$dateTo'";
        } elseif ($dateFrom && !$dateTo) {
            $addToSelect = " and created_at >= '$dateFrom'";
        } elseif (!$dateFrom && $dateTo) {
            $addToSelect = " and created_at <= '$dateTo'";
        }
        return $addToSelect;
    }
}