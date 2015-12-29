<?php

class Wyomind_Advancedinventory_Model_Api extends Mage_Api_Model_Resource_Abstract {

    public function setManageLocalStock($product_id, $status) {
        try {
            $item = Mage::getModel('advancedinventory/item')->loadByProductId($product_id);


            if ($status) {
                $item->setData(array("id" => $item->getId(), "product_id" => $product_id, "manage_local_stock" => $status))->save();
                $this->insertLogRow("API", "Set Multi-stock", "P#$product_id", "Enable multi-stock");
                foreach ($this->getWhPos() as $pos) {
                    $this->setStockData($product_id, $pos["place_id"]);
                }
                $inventory = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product_id);
                $inventory->setQty(0)->save();
                $this->insertLogRow("API", "Set Multi-stock", "P#$product_id", "Qty : 0");
            } else {
                $item->delete();
                $this->insertLogRow("API", "Set Multi-stock", "P#$product_id", "Disable multi-stock");
            }
            return true;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public function setStockData($product_id, $warehouse_id, $data = array("manage_stock" => 1, "quantity_in_stock" => 0, "backorder_allowed" => 0, "use_config_setting_for_backorders" => 1)) {

        try {
            $stock = Mage::getModel('advancedinventory/stock')->getStockByProductIdAndPlaceId($product_id, $warehouse_id);


            $origin_qty = $stock->getQuantityInStock();
            $data["id"] = $stock->getId();
            $data["place_id"] = $warehouse_id;
            $data["product_id"] = $product_id;
            $data["localstock_id"] = Mage::getModel('advancedinventory/item')->loadByProductId($product_id)->getId();

            if ($stock->getQuantity_in_stock() != $data['quantity_in_stock'] || $stock->getUse_config_setting_for_backorders() != $data['use_config_setting_for_backorders'] || $stock->getManageStock() != $data['manage_stock'] || $stock->getBackorder_allowed() != $data['backorder_allowed']) {
                $this->insertLogRow("API", "Set Data", "P#$product_id,W#$warehouse_id", "Qty : " . (int) $stock->getQuantity_in_stock() . " -> " . $data['quantity_in_stock'] . ", Manage stock = " . (int) $data['manage_stock'] . ", Backorder = " . $data['backorder_allowed'] . ", Use config setting = " . $data['use_config_setting_for_backorders']);
                $stock->setData($data)->save();
            }

            $inventory = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product_id);
            $new_qty = $inventory->getQty() - $origin_qty + $data['quantity_in_stock'];


            if ((int) $inventory->getQty() != $new_qty) {
                $this->insertLogRow("API", "Set Data", "P#$product_id", "Qty : " . (int) $inventory->getQty() . " -> " . $new_qty);
            }
            $inventory->setQty($new_qty);
            $inventory->save();

            return true;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public function getWhPos() {
        try {
            $array = array();
            foreach (Mage::getModel('pointofsale/pointofsale')->getCollection()as $warehouse) {
                $array[] = $warehouse->getData();
            }
            return $array;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public function getStockData($product_id, $warehouse_id) {
        return Mage::getModel('advancedinventory/stock')->getStockByProductIdAndPlaceId($product_id, $warehouse_id)->getData();
    }

    Public function insertLogRow($context, $action, $reference, $details, $user = false) {
        try {
            if(!$user)
                $user = "Api : " . Mage::getSingleton('api/session')->getUser()->getData("username");
            Mage::helper('advancedinventory/log')->insertRow($context, $action, $reference, $details, $user);
            return true;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

}
