<?php

class Wyomind_Advancedinventory_Adminhtml_StocksController extends Mage_Adminhtml_Controller_Action {

    protected function _initAction() {
        $this->_title($this->__('Stocks'))->_title($this->__('Manage Products'));
        $this->loadLayout()
                ->_setActiveMenu("catalog/stocks");
        return $this;
    }

    public function indexAction() {

        $this->_initAction()
                ->renderLayout();
    }

    public function treeviewAction() {
        $type = Mage::app()->getRequest()->getParam("type");
        $productId = Mage::app()->getRequest()->getParam("productid");
        $instanceId = Mage::app()->getRequest()->getParam("instanceid");

        $nodes = array();
        if ($type == 'storegroup') {
            $website = Mage::getModel('core/website')->load($instanceId);
            $storegroups = $website->getGroupCollection();
            foreach ($storegroups as $storegroup) {
                $g[$storegroup->getId()] = array();
                $stores = $storegroup->getStores();
                foreach ($stores as $store) {
                    $g[$storegroup->getId()][] = $store->getId();
                }
            }

            foreach ($storegroups as $storegroup) {

                $qty = (int) Mage::getModel("advancedinventory/stock")->getStockByProductIdAndStoresId($productId, $g[$storegroup->getId()])->getQty();

                $nodes[] = "{
                    'id': 'g" . $storegroup->getId() . "-p" . $productId . "',
                    'txt': '<b>" . $storegroup->getName() . " (" . $qty . ")</b>',
                    'onopenpopulate' : myOpenPopulate,
                    'openlink' : '" . Mage::getUrl('advancedinventory/adminhtml_stocks/treeview', array("type" => "stores", 'instanceid' => $storegroup->getId(), "productid" => $productId)) . "',
                    'canhavechildren' : true
                    }";
            }
        } else if ($type == "stores") {

            $storegroup = Mage::getModel('core/store_group')->load($instanceId);
            $stores = $storegroup->getStoreCollection();

            foreach ($stores as $store) {
                $qty = Mage::getModel("advancedinventory/stock")->getStockByProductIdAndStoresId($productId, $store->getId())->getQty();

                $nodes[] = "{
                    'id': 's" . $store->getId() . "-p" . $productId . "',
                    'txt': '" . $store->getName() . " (" . (int) $qty . ")',
                    'onopenpopulate' : myOpenPopulate,
                    'openlink' : '" . Mage::getUrl('advancedinventory/adminhtml_stocks/treeview', array("type" => "pos", 'instanceid' => $store->getId(), "productid" => $productId)) . "',
                    'canhavechildren' : true
                    }";
            }
        } else if ($type == 'pos') {
            $stores = Mage::getModel("pointofsale/pointofsale")->getPlacesByStoreId($instanceId);


            foreach ($stores as $store) {
                $s = Mage::getModel("advancedinventory/stock")->getStockByProductIdAndPlaceId($productId, $store->getId());
                $qty = $s->getQuantityInStock();
                if (!$s->getManageStock())
                    $qty = Mage::helper('advancedinventory')->__("X");
                $name = substr($store->getName(), 0, 20 - 3);
                $s = strrpos($name, " ");
                $name = substr($name, 0, $s) . '...';


                $name = substr($store->getName(), 0, 20 - 3);
                $s = strrpos($name, " ");
                $name = substr($name, 0, $s) . '...';
                $nodes[] = "{
                    'id': 's-" . $instanceId . "pos" . $store->getId() . "-p" . $productId . "',
                    'txt': '<i>" . $name . " (" . $qty . ")</i>',
                     }";
            }
        }
        die("[" . implode(',', $nodes) . "]");
    }

    public function detailsAction() {
        try {
            $this->loadLayout();
            $block = $this->getLayout()->createBlock(
                    'Mage_Core_Block_Template', 'assignation_popup', array('template' => 'stocks/assignation.phtml')
            );
            die(Mage::helper('core')->jsonEncode(array("error" => 'false', "content" => $block->toHtml())));
        } catch (Exception $exception) {
            die(Mage::helper('core')->jsonEncode(array("error" => 'true', "content" => $exception->getMessage())));
        }
    }

    public function assignationAction() {




        $data_json = $this->getRequest()->getPost('data');
        $data_to = Mage::helper('core')->jsonDecode($data_json);
        try {
            $data_from = Mage::helper('core')->jsonDecode($this->getRequest()->getPost('data_origin'));
        } catch (Exception $e) {
            $data_from = array();
        }

        $order_id = $this->getRequest()->getPost('order_id');



        $actions = array();
        $wh = array();
        foreach ($data_to as $product_id => $warehouses) {
            foreach ($warehouses as $wh_id => $qty) {
                $actions[$product_id][$wh_id] = (int) @$data_from[$product_id][$wh_id] - $qty;
                if ($qty > 0)
                    $wh[] = $wh_id;
            }
        };
        foreach ($actions as $product_id => $warehouses) {
            $update = 0;
            foreach ($warehouses as $wh_id => $qty) {

                $stock = Mage::getModel('advancedinventory/stock')->getStockByProductIdAndPlaceId($product_id, $wh_id);

                if ($stock->getManageStock()) {
                    $qty_origin = $stock->getQuantityInStock();
                    $data = array(
                        "id" => $stock->getId(),
                        "quantity_in_stock" => $qty_origin + $qty
                    );

                    $update -= $data_to[$product_id][$wh_id] - $data_from[$product_id][$wh_id];
                    if ($data_to[$product_id][$wh_id] != $data_from[$product_id][$wh_id]) {
                        Mage::helper('advancedinventory/log')->insertRow("Order grid", "Assignation updated", "O#$order_id,P#$product_id,W#$wh_id ", "Assigned qty : " . $data_from[$product_id][$wh_id] . " -> " . $data_to[$product_id][$wh_id]);
                        Mage::helper('advancedinventory/log')->insertRow("Order grid", "Qty updated", "P#$product_id,W#$wh_id ", "Qty : $qty_origin -> " . ($qty_origin + $qty));
                        $stock->setData($data)->save();
                    }
                }
            }
            if (abs($update) > 0) {
                $inventory = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product_id);
                $new_qty = $inventory->getQty() + $update;
                Mage::helper('advancedinventory/log')->insertRow("Order grid", "Total qty updated", "P#$product_id", "Qty : " . (int) $inventory->getQty() . " -> " . ($new_qty));
                $inventory->setQty($new_qty)->save();
            }
        }

        $orderedItems = Mage::helper('advancedinventory/data')->getOrderedItems(Mage::getModel('sales/order')->load($order_id));

        $assignation_required = 0;
        foreach ($orderedItems as $item) {
            if (count($data_to[$item['id']])) {
                $assignation_required+=count($data_to[$item['id']]);
                if ($item["qty"] != array_sum($data_to[$item['id']]))
                    $wh[] = 0;
            }
        }

        if (!$assignation_required) {
            $wh = array();
        }

        $order = Mage::getModel('sales/order');
        $order->load($order_id);
        $order->setAssignationWarehouse(implode(',', (array_unique($wh))))->save();
        $order->setAssignationStock($data_json)->save();

        $render = new Wyomind_Advancedinventory_Block_Adminhtml_Sales_Order_Renderer_Assignation;
        die($render->render($order));
    }

    public function MassEnableAction() {
        $product_ids = $this->getRequest()->getPost('product_id');
        try {
            $pos = Mage::getModel("pointofsale/pointofsale")->getCollection();
            foreach ($product_ids as $product_id) {
                $inventory = Mage::getModel('cataloginventory/stock_item')->loadByProduct(Mage::getModel('catalog/product')->load($product_id));
                $inventory->setBackorders(1)->setUseConfigBackorders(0)->save();
                $stock = Mage::getModel("advancedinventory/item")->loadByProductId($product_id);
                $data = array(
                    "id" => $stock->getId(),
                    "product_id" => $product_id,
                    "manage_local_stock" => true,
                );
                $stock_id = $stock->setData($data)->save()->getId();
                $model = Mage::getModel("advancedinventory/stock");
                foreach ($pos as $wh) {
                    $stock = $model->getStockByProductIdAndPlaceId($product_id, $wh->getPlaceId());
                    $data = array(
                        "id" => $stock->getId(),
                        "localstock_id" => $stock_id,
                        "place_id" => $wh->getPlaceId(),
                        "product_id" => $product_id,
                    );

                    $model->setData($data)->save();
                }
                Mage::helper('advancedinventory/log')->insertRow("Stock grid", "Mass action", "P#$product_id", "Enable multi-stock");
            }
        } catch (Exception $exception) {
            Mage::getSingleton('adminhtml/session')->addError($exception->getMessage());
        }
        $this->_redirect('*/*/index');
    }

    public function MassDisableAction() {
        $product_ids = $this->getRequest()->getPost('product_id');
        try {
            foreach ($product_ids as $product_id) {
                $stock = Mage::getModel("advancedinventory/item")->loadByProductId($product_id);
                $stock->delete();
                Mage::helper('advancedinventory/log')->insertRow("Stock grid", "Mass action", "P#$product_id", "Disable multi-stock");
            }
        } catch (Exception $exception) {
            Mage::getSingleton('adminhtml/session')->addError($exception->getMessage());
        }
        $this->_redirect('*/*/index');
    }

    public function saveAction() {
         
        try {


            $data = Mage::helper('core')->jsonDecode($this->getRequest()->getPost('data'));
            $store_id = $this->getRequest()->getParam('store_id');
            $is_admin = $this->getRequest()->getParam('is_admin');
            $model = Mage::getModel("advancedinventory/stock");
            foreach ($data as $product_id => $product_data) {

                $multistock_enabled = $product_data['multistock'];
                $pos_wh = $product_data['pos_wh'];
                $qty = $product_data['qty'];
                $product = Mage::getModel('catalog/product')->load($product_id);
                $inventory = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);

                if ($multistock_enabled) {
                    $qty = 0;
                    $substract = 0;

                    foreach ($pos_wh as $place_id => $stock) {
                        if ($store_id || !$is_admin) {
                            $substract+=$model->getStockByProductIdAndPlaceId($product_id, $place_id)->getQuantityInStock();
                        }
                        $qty+=$stock["qty"];
                    }
                }

                if ($store_id != 0 || !$is_admin) {
                    $qty = $inventory->getQty() - $substract + $qty;
                }


                $stock = Mage::getModel("advancedinventory/item")->loadByProductId($product_id);
                if ($multistock_enabled) {


                    $data = array(
                        "id" => $stock->getId(),
                        "product_id" => $product_id,
                        "manage_local_stock" => ($multistock_enabled) ? true : false,
                    );
                    if ($stock->getManageLocalStock() != $multistock_enabled) {
                        Mage::helper('advancedinventory/log')->insertRow("Stock grid", "Status updated", "P#$product_id", "Enable multi-stock");
                    }
                    $stock_id = $stock->setData($data)->save()->getId();

                    $is_in_stock = false;
                    foreach ($pos_wh as $pos_id => $invData) {

                        $stock = $model->getStockByProductIdAndPlaceId($product_id, $pos_id);

                        $data = array(
                            "id" => $stock->getId(),
                            "localstock_id" => $stock_id,
                            "place_id" => $pos_id,
                            "product_id" => $product_id,
                            "quantity_in_stock" => $invData['qty'],
                        );
                        if (!$stock->getManage_stock() || $stock->getBackorder_allowed() > 0 || ($stock->getUse_config_setting_for_backorders() && Mage::getStoreConfig("cataloginventory/item_options/backorders")))
                            $is_in_stock = true;
                       
                        $model->setData($data);
                        if ($stock->getQuantity_in_stock() != $invData['qty']) {
                            Mage::helper('advancedinventory/log')->insertRow("Stock grid", "Qty updated", "P#$product_id,W#$pos_id", "Qty : " . (int) $stock->getQuantity_in_stock() . " -> " . $invData['qty']);
                        }
                        $model->save();
                    }
                   
                    $inventory->setBackorders(1)->setUseConfigBackorders(0)->save();
                } else {
                    if ($stock->getManageLocalStock() != $multistock_enabled) {
                        Mage::helper('advancedinventory/log')->insertRow("Stock grid", "Status updated", "P#$product_id", "Disable multi-stock");
                    }
                    $stock->delete();
                }
                if ($inventory->getQty() != $qty) {

                    Mage::helper('advancedinventory/log')->insertRow("Stock grid", "Total qty updated", "P#$product_id", "Qty : " . (int) $inventory->getQty() . " -> " . $qty);
                }
                if (!Mage::getStoreConfig("advancedinventory/setting/auto_update_stock_status")) {
                    $permissions = Mage::helper('advancedinventory/permissions')->getUserPermissions();
                    $all = $permissions->isAdmin();
                    if ($all){
                        $stockStatus = ($product_data["is_in_stock"] == "true") ? true : false;
                    }
                    else{
                        
                        $stockStatus = $inventory->getIsInStock();
                    }
                } else {
                    $minQty = ($inventory->getUseConfigMaxSaleQty()) ? Mage::getStoreConfig("cataloginventory/item_options/min_qty") : $inventory->getMinSaleQty();
                    if ($qty > $minQty ||  $is_in_stock) {
                        $stockStatus = true;
                    } else {
                        $stockStatus = false;
                    }
                }
                if ($stockStatus != $inventory->getIsInStock()) {
                    $status = ($stockStatus) ? "In stock" : "Out of stock";
                    Mage::helper('advancedinventory/log')->insertRow("Stock grid", "Status updated", "P#$product_id", $status);
                }
                $inventory
                        ->setIsInStock($stockStatus)
                        ->setQty($qty)
                        ->save();
            }

            die(Mage::helper('core')->jsonEncode(array("error" => false, "message" => "Data saved")));
        } catch (Exception $exception) {
            die(Mage::helper('core')->jsonEncode(array("error" => true, "message" => $exception->getMessage())));
        }
    }

}
