<?php

class Wyomind_Advancedinventory_Model_Stock extends Mage_Core_Model_Abstract {

    public function _construct() {
        parent::_construct();
        $this->_init('advancedinventory/stock');
    }

    // Stocks par produit et pour un/plusieurs storeviews (1/+ storeview(s) => 1 stock total)
    public function getStockByProductIdAndStoresId($productId, $storeIds) {
        $advancedinventory = Mage::getSingleton('core/resource')->getTableName('advancedinventory_stock');
        $advancedinventory_item = Mage::getSingleton('core/resource')->getTableName('advancedinventory_item');

        $collection = Mage::getModel('pointofsale/pointofsale')->getCollection();
        $collection->getSelect()
                ->joinLeft(array("lsp" => $advancedinventory_item), "lsp.product_id = $productId")
                ->joinLeft(
                        array(
                    "stocks" => $advancedinventory), "stocks.place_id = main_table.place_id AND stocks.product_id='$productId'", array(
                    "qty" => "SUM(stocks.quantity_in_stock )"
                        )
        );
        if (!is_array($storeIds)) {
            $collection->getSelect()
                    ->where("FIND_IN_SET(" . $storeIds . ",main_table.store_id) ")->group('product_id');
        } else {
            foreach ($storeIds as $storeId) {
                $where[] = "FIND_IN_SET(" . $storeId . ",main_table.store_id) ";
            }
            $collection->getSelect()
                    ->where(implode(' OR ', $where))->group('product_id');
        }
        return $collection->getFirstItem();
    }

    // Stocks par produit et pour un wh/pos (1 pos/wh => 1 stock total)
    public function getStockByProductIdAndPlaceId($product_id, $place_id) {
        $advancedinventory_item = Mage::getSingleton('core/resource')->getTableName('advancedinventory_item');
        $collection = Mage::getModel('advancedinventory/stock')->getCollection()
                ->addFieldToFilter('main_table.product_id', Array('eq' => $product_id))
                ->addFieldToFilter('place_id', Array('eq' => $place_id));
        $collection->getSelect()->joinLeft(
                array("lsp" => $advancedinventory_item), "lsp.product_id = $product_id", array(
            "manage_local_stock" => "lsp.manage_local_stock",
                )
        );

        return $collection->getFirstItem();
    }

    // Manage multi-stock par produit 
    public function getMultiStockEnabledByProductId($product_id) {
        $collection = Mage::getModel('advancedinventory/item')->getCollection()
                ->addFieldToFilter('product_id', Array('eq' => $product_id));

        return $collection->getFirstItem()->getManageLocalStock();
    }

    // Get all pos/wh stocks by product id (1 storeview => stock/pos))
    public function getStocksByProductIdAndStoreId($product_id, $storeId = 0) {


        $advancedinventory = Mage::getSingleton('core/resource')->getTableName('advancedinventory_stock');
        $advancedinventory_item = Mage::getSingleton('core/resource')->getTableName('advancedinventory_item');
        //$product_id = Mage::app()->getrequest()->getParam('id');
        $collection = Mage::getModel('pointofsale/pointofsale')->getCollection();
        if ($storeId) {
            $collection->getSelect()
                    ->where("FIND_IN_SET(" . $storeId . ",main_table.store_id) ");
        }
        $collection->getSelect()->from(null, array('main_table.place_id', 'name', 'store_code', 'status'))
                ->joinLeft(
                        array("lsp" => $advancedinventory_item), "lsp.product_id = '$product_id'", array(
                    "manage_local_stock" => "lsp.manage_local_stock",
                    "product_id" => "product_id",
                    "stock_product_id" => "id"
                        )
                )
                ->joinLeft(
                        array("stocks" => $advancedinventory), "stocks.place_id = main_table.place_id AND stocks.product_id='$product_id'", array(
                    "qty" => "if(stocks.quantity_in_stock IS NOT NULL,stocks.quantity_in_stock,0)",
                    "stock_id" => "stocks.id",
                    "manage_stock" => "stocks.manage_stock",
                    "backorder_allowed" => "stocks.backorder_allowed",
                    "use_config_setting_for_backorders" => "stocks.use_config_setting_for_backorders"
                        )
                )
                ->group(array("lsp.product_id", "main_table.place_id"))->order("main_table.position");
     
        return $collection;
    }

}
