<?php

class Wyomind_Advancedinventory_Model_Advancedinventory extends Mage_Core_Model_Abstract {

    public function _construct() {
        parent::_construct();
        $this->_init('advancedinventory/advancedinventory');
    }

    public function getLocalStockQtyForAllStoreView($product_id) {
       
        
        $advancedinventory = Mage::getSingleton('core/resource')->getTableName('advancedinventory');
        $advancedinventory_product = Mage::getSingleton('core/resource')->getTableName('advancedinventory_product');
        $product_id = Mage::app()->getrequest()->getParam('id');
        $collection = Mage::getModel('pointofsale/pointofsale')->getCollection(); //->addFieldToSelect(array('place_id', 'name', 'store_code', 'status'));
        $collection->getSelect()->from(null, array('main_table.place_id', 'name', 'store_code', 'status'))
                ->joinLeft(
                        array("lsp" => $advancedinventory_product), "lsp.product_id = $product_id", array(
                    "manage_local_stock" => "lsp.manage_local_stock",
                    "product_id" => "product_id",
                    "stock_product_id" => "id"
                        )
                )
                ->joinLeft(
                        array("stocks" => $advancedinventory), "stocks.place_id = main_table.place_id AND stocks.product_id=$product_id", array(
                    "qty" => "if(stocks.quantity_in_stock IS NOT NULL,stocks.quantity_in_stock,0)",
                    "stock_id" => "stocks.id",
                    "backorder_allowed" => "stocks.backorder_allowed",
                    "use_config_setting_for_backorders" => "stocks.use_config_setting_for_backorders"
                        )
                )
                
                ->group(array("lsp.product_id", "main_table.place_id"));
        return $collection;
    }

    public function getLocalStockQtySum($product_id) {
       
       
        
        $collection = Mage::getModel('advancedinventory/advancedinventory')->getCollection()
                ->addFieldToFilter('product_id', Array('eq' => $product_id))
                ->addExpressionFieldToSelect('stock', "SUM(quantity_in_stock)");
        $collection->getSelect()
                ->group(array("product_id"));
        return $collection->getFirstItem();
    }

    public function getLocalStockQty($product_id, $place_id) {
        $advancedinventory_product = Mage::getSingleton('core/resource')->getTableName('advancedinventory_product');
        $collection = $this->getCollection()
                ->addFieldToFilter('main_table.product_id', Array('eq' => $product_id))
                ->addFieldToFilter('place_id', Array('eq' => $place_id));
        $collection->getSelect()->joinLeft(
                array("lsp" => $advancedinventory_product), "lsp.product_id = $product_id", array(
            "manage_local_stock" => "lsp.manage_local_stock",
                )
        );
        return $collection->getFirstItem();
    }

    public function getLocalGlobalStockByProductId($product_id) {
        $collection = Mage::getModel('advancedinventory/advancedinventoryproduct')->getCollection()
                ->addFieldToFilter('product_id', Array('eq' => $product_id));

        return $collection->getFirstItem();
    }

    public function getLocalStockByProductIdAndPlaceId($product_id, $place_id) {
        $collection = Mage::getModel('advancedinventory/advancedinventory')->getCollection()
                ->addFieldToFilter('product_id', Array('eq' => $product_id))
                ->addFieldToFilter('place_id', Array('eq' => $place_id));

        return $collection->getFirstItem();
    }

}