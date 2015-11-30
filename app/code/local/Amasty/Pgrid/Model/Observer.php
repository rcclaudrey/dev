<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Pgrid
 */
class Amasty_Pgrid_Model_Observer
{
    public function reindexQtySold($observer)
    {
        $quote = $observer->getEvent()->getQuote();
        $productIds = array();
        foreach ($quote->getAllItems() as $item) {
            $productIds[$item->getProductId()] = $item->getProductId();
            $children   = $item->getChildrenItems();
            if ($children) {
                foreach ($children as $childItem) {
                    $productIds[$childItem->getProductId()] = $childItem->getProductId();
                }
            }
        }
        if (count($productIds)) {
            Mage::getSingleton('ampgrid/sold_indexer')->reindexQtySold($productIds);
        }
        return $this;
    }

    public function refundOrderInventory($observer)
    {
        $creditmemo = $observer->getEvent()->getCreditmemo();
        $orderData = array();
        foreach ($creditmemo->getAllItems() as $item) {
            $children   = $item->getChildrenItems();
            $orderData[$item->getProductId()]['qty'] = $item->getQty();
            $orderData[$item->getProductId()]['order_item_id'] = $item->getOrderItemId();
            if ($children) {
                foreach ($children as $childItem) {
                    $orderData[$item->getProductId()]['qty'] = $item->getQty();
                    $orderData[$item->getProductId()]['order_item_id'] = $item->getOrderItemId();
                }
            }
        }
        if (count($orderData)) {
            Mage::getSingleton('ampgrid/sold_indexer')->revertProductsSale($orderData);
        }
    }

    public function addNoticeIndex($observer)
    {
        if (Mage::getStoreConfig('ampgrid/additional/qty_sold')) {
            $section = $observer->getSection();
            if ($section == 'ampgrid') {
                Mage::helper('ampgrid')->addNoticeIndex();
            }
        }
    }

    public function catalogProductPrepareSave(Varien_Event_Observer $observer) {
        if($observer->getRequest()->getModuleName() == 'ampgrid') {
            $date = $observer->getProduct()->getData('created_at');
            $observer->getProduct()->setData('created_at', strtotime($date));
        }

    }
}