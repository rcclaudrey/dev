<?php

class Wyomind_Advancedinventory_Model_Observer {

    function setAssignationM2e($observer) {
        $order = $observer->getEvent()->getOrder()->getMagentoOrder();
        $orderedItems = Mage::helper('advancedinventory/data')->getOrderedItems($order);
        $result = Mage::helper('advancedinventory/data')->getAssignation($order, $orderedItems);
        $order->setAssignationWarehouse(implode(",", $result->assignation_warehouses))->setAssignationStock(Mage::helper('core')->jsonEncode($result->assignation_stock))->save();
    }

    function setAssignation($observer) {

        $order = $observer->getEvent()->getOrder();

        if (!$order) {
            $orders = $observer->getEvent()->getOrders();
        } else {
            $orders = array($order);
        }

        foreach ($orders as $order) {


            if (!$order->getAssignationStock()) {

                if (Mage::getStoreConfig("advancedinventory/setting/autoassign_order")) {
                    $orderedItems = Mage::helper('advancedinventory/data')->getOrderedItems($order);

                    $result = Mage::helper('advancedinventory/data')->getAssignation($order, $orderedItems);

                    $order->setAssignationWarehouse(implode(",", $result->assignation_warehouses))->setAssignationStock(Mage::helper('core')->jsonEncode($result->assignation_stock))->save();


                    $storeId = $order->getStore()->getId();

                    if (!Mage::helper('sales')->canSendNewOrderEmail($storeId))
                        return;
                    foreach ($result->assignation_warehouses as $warehouse_id) {
                        // Get the destination email addresses to send copies to
                        $emails = explode(',', trim(Mage::getModel('pointofsale/pointofsale')->load($warehouse_id)->getInventoryNotification()));

                        if (count($emails) > 0 && $order->getState() != Mage_Sales_Model_Order::STATE_CANCELED && $emails[0] != '') {

                            if (version_compare(Mage::getVersion(), '1.5.0', '>=')) {                            // Start store emulation process
                                $appEmulation = Mage::getSingleton('core/app_emulation');
                                $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);

                                try {
                                    // Retrieve specified view block from appropriate design package (depends on emulated store)
                                    $paymentBlock = Mage::helper('payment')->getInfoBlock($order->getPayment())
                                            ->setIsSecureMode(true);
                                    $paymentBlock->getMethod()->setStore($storeId);
                                    $paymentBlockHtml = $paymentBlock->toHtml();
                                } catch (Exception $exception) {
                                    // Stop store emulation process
                                    $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
                                    throw $exception;
                                }

                                // Stop store emulation process
                                $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
                            }
                            // Retrieve corresponding email template id and customer name
                            if ($order->getCustomerIsGuest()) {
                                $templateId = Mage::getStoreConfig('sales_email/order/guest_template', $storeId);
                                $customerName = $order->getBillingAddress()->getName();
                            } else {
                                $templateId = Mage::getStoreConfig('sales_email/order/template', $storeId);
                                $customerName = $order->getCustomerName();
                            }

                            $mailer = Mage::getModel('core/email_template_mailer');
                            $emailInfo = Mage::getModel('core/email_info');
                            $emailInfo->addTo($emails[0], $customerName);
                            if (count($emails) > 0) {
                                $c = 0;
                                foreach ($emails as $email) {
                                    if ($c > 0)
                                        $emailInfo->addBcc($email);
                                    $c++;
                                }
                            }
                            $mailer->addEmailInfo($emailInfo);



                            // Set all required params and send emails
                            $mailer->setSender(Mage::getStoreConfig('sales_email/order/identity', $storeId));
                            $mailer->setStoreId($storeId);
                            $mailer->setTemplateId($templateId);
                            $mailer->setTemplateParams(array(
                                'order' => $order,
                                'billing' => $order->getBillingAddress(),
                                'payment_html' => $paymentBlockHtml
                                    )
                            );
                            $mailer->send();
                        }
                    }
                }
            }
        }
        return;
    }

    protected function _getStore() {
        $storeId = (int) Mage::app()->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }

    function cancel($observer) {
        $order = $observer->getEvent()->getPayment()->getOrder();


        $orderedItems = Mage::helper('advancedinventory/data')->getOrderedItems($order);
        try {
            $data = Mage::helper('core')->jsonDecode($order->getAssignationStock());
        } catch (Exception $e) {
            Mage::log('Advanced Inventory says :' . $exception->getMessage(), Zend_Log::ERR);
        }
        if (Mage::app()->getStore()->isAdmin()) {
            $type = "Backend";
        } else {
            $type = "Frontend";
        }
        $additional = array("action" => "Order canceled", "type" => $type, "store_id" => $order->getStoreId(), "order_id" => $order->getId());
        Mage::helper('advancedinventory/data')->increaseStock($orderedItems, $data, $additional);
        $order->setAssignationStock(null)->save();
    }

    public function refund($observer) {

        $creditmemo = $observer->getEvent()->getCreditmemo();
        $order = $observer->getEvent()->getCreditmemo()->getOrder();
        $orderedItems = Mage::helper('advancedinventory/data')->getOrderedItems($order);


        try {
            $assignation_stock_origin = Mage::helper('core')->jsonDecode($order->getAssignationStock());
        } catch (Exception $e) {
            Mage::log('Advanced Inventory says :' . $exception->getMessage(), Zend_Log::ERR);
        }



        $assignation_stock_new = $assignation_stock_origin;
        $orderedItems_new = $orderedItems;
        $items = $creditmemo->getAllItems();
        $i = 0;
        foreach ($items as $item) {


            if ($item->getOrderItem()->getParentItemId() != null && $item->getOrderItem()->getParentItem()->getProductType() == "configurable") {

                $itemQty = $items[$i - 1]->getQty();
            } else {
                $itemQty = $item->getQty();
            }
            $i++;
            $remain = $itemQty;
            $orderedItems[$item->getProductId()]["qty"]+=$itemQty;

            $return = false;
            $back_in_stock = array_reverse($assignation_stock_origin[$item->getProductId()], true);


            foreach ($back_in_stock as $wh => $qty) {

                if ($item->getBackToStock() && $itemQty) { //qtyRefunded ?
                    $assignation_stock_origin[$item->getProductId()][$wh] = $qty;
                } else {
                    $assignation_stock_origin[$item->getProductId()][$wh] = 0;
                }

                if ($qty >= $remain && $remain) {
                    $assignation_stock_new[$item->getProductId()][$wh] = $qty - $itemQty;
                    $warehouses[] = $wh;
                    $remain = 0;
                } elseif ($qty < $remain && $remain) {
                    $assignation_stock_new[$item->getProductId()][$wh] = 0;
                    $remain = $itemQty - $qty;
                } else {
                    $assignation_stock_new[$item->getProductId()][$wh] = $qty;
                    $warehouses[] = $wh;
                }
            }

            if (isset($assignation_stock_new[$item->getProductId()]))
                $assignation_stock_new[$item->getProductId()] = array_reverse($assignation_stock_new[$item->getProductId()], true);
        }
        

        $additional = array("action" => "Order creditmemo", "type" => "Backend", "store_id" => $order->getStoreId(), "order_id" => $order->getId());
        Mage::helper('advancedinventory/data')->increaseStock($orderedItems, $assignation_stock_origin, $additional);
        $additional = array("action" => "Order creditmemo", "type" => "Backend", "store_id" => $order->getStoreId(), "order_id" => $order->getId(), "warehouses" => $warehouses);
        if (count($warehouses))
            Mage::helper('advancedinventory/data')->decreaseStock($orderedItems_new, $assignation_stock_new, $additional);

        $order->setAssignationWarehouse(implode(',', array_unique($warehouses)))->setAssignationStock(Mage::helper('core')->jsonEncode($assignation_stock_new))->save();
    }

    function getAvailability($product, $type = null) {
        $productId = $product->getId();
        $modelInventory = Mage::getModel("cataloginventory/stock_item")->loadByProduct($productId);
        if (!$modelInventory->getIsInStock())
            return false;
        $stores = Mage::getModel('pointofsale/pointofsale')->getPlacesByStoreId(Mage::app()->getStore()->getStoreId());
        $qty = 0;
        $backOrderAllowed = 0;
        $useDefaultConfigBackOrder = 0;
        $manageLocalStock = 0;

        $defaultBackOrdeValue = Mage::getStoreConfig("cataloginventory/item_options/backorders");

        $minQty = $modelInventory->getMinQty();

        $rtn = $product->isAvailable();

        foreach ($stores as $s) {

            $stock = Mage::getModel('advancedinventory/stock')->getStockByProductIdAndPlaceId($productId, $s['place_id']);

            $backOrderAllowed = $stock->getBackorder_allowed();
            $useDefaultConfigBackOrder = $stock->getUse_config_setting_for_backorders();
            $qty += $stock->getQuantity_in_stock();
            $manageLocalStock = $stock->getManage_local_stock();

            if ($manageLocalStock) {

                $rtn = true;
                if (!$stock->getManageStock())
                    $rtn = false;
                else if ($qty > $minQty)
                    return true;
                else if ($qty <= $minQty && $useDefaultConfigBackOrder && $defaultBackOrdeValue)
                    return true;
                else if ($qty <= $minQty && !$useDefaultConfigBackOrder && $backOrderAllowed)
                    return true;
                else
                    $rtn = false;
            }
        }

        return $rtn;
    }

    function saleable($observer) {

        if (Mage::getStoreConfig("advancedinventory/setting/usemultistock")) {
            $rtn = false;
            $product = $observer->getProduct();

            if ($product->isConfigurable()) {

                $modelInventory = Mage::getModel("cataloginventory/stock_item")->loadByProduct($product);
                if (!$modelInventory->getIsInStock()) {
                    $observer->getSalable()->setIsSalable(false);
                    return;
                }
                $AssociatedProduct = $product->getTypeInstance()->getUsedProducts();

                foreach ($AssociatedProduct as $child) {
                    $rtn+=$this->getAvailability($child);
                    if ($rtn)
                        break;
                };
            } else {
                $rtn = $this->getAvailability($product);
            }

            $observer->getSalable()->setIsSalable($rtn);
        }
    }

    public function addActionColumn(Varien_Event_Observer $observer) {

        $block = $observer->getEvent()->getBlock();
        $this->_block = $block;


        if (get_class($block) == Mage::getStoreConfig("advancedinventory/system/productgrid") && Mage::getStoreConfig("advancedinventory/system/enableproductgrid")) {
            $block->setUseAjax(false);
            if (Mage::app()->getRequest()->getParam('store')) {
                $width = 250;
            } else {
                $width = 150;
            }

            $permissions = Mage::helper('advancedinventory/permissions')->getUserPermissions();
            $all = $permissions->isAdmin();
            $position = ($all) ? "qty" : "status";
            if (!$all) {
                $block->removeColumn('qty');
            }

            $block->addColumnAfter("treeview", array(
                "header" => Mage::helper("advancedinventory")->__("Stocks"),
                "width" => $width . "px",
                "type" => "text",
                "align" => "center",
                "filter" => false,
                "sortable" => false,
                "renderer" => "Wyomind_Advancedinventory_Block_Adminhtml_Catalog_Product_Renderer_Treeview",
                    ), $position);

            $block->sortColumnsByOrder();
        }

        if (get_class($block) == Mage::getStoreConfig("advancedinventory/system/ordergrid")) {

            $permissions = Mage::helper('advancedinventory/permissions')->getUserPermissions();
            $all = $permissions->isAdmin();
            $pos = $permissions->getPos();


            if (Mage::getModel('pointofsale/pointofsale')->getPlaces()->count() > 0) {
                $places = Mage::getModel('pointofsale/pointofsale')->getPlaces();
                if (in_array(0, $pos) || $all) {
                    $inventories[0] = Mage::helper('advancedinventory')->__('Not Assigned');
                }
                foreach ($places as $p) {
                    if (in_array($p->getPlaceId(), $pos) || $all) {
                        $inventories[$p->getPlaceId()] = $p->getName() . ' (' . $p->getStoreCode() . ')';
                    }
                }

                $block->addColumnAfter('assignation_warehouse', array(
                    'header' => Mage::helper('sales')->__('Assignation'),
                    'index' => 'assignation_warehouse',
                    'type' => 'options',
                    'width' => '200px',
                    'options' => $inventories,
                    'sortable' => false,
                    'filter_condition_callback' => array($this, '_filterAssignationCondition'),
                    'renderer' => "Wyomind_Advancedinventory_Block_Adminhtml_Sales_Order_Renderer_Assignation",
                        ), 'status');
            }
            $block->sortColumnsByOrder();
        }


        return $observer;
    }

    function _filterAssignationCondition($collection, $column) {

        $value = $column->getFilter()->getValue();

        if ($value == '') {
            return;
        } else
            $collection->addFieldToFilter('assignation_warehouse', array('finset' => $value));
    }

    public function prepareOrderGridCollection($observer) {

        $collection = $observer->getOrderGridCollection();



        $permissions = Mage::helper('advancedinventory/permissions')->getUserPermissions();
        $all = $permissions->isAdmin();
        $pos = $permissions->getPos();

        parse_str(urldecode(base64_decode(Mage::app()->getRequest()->getParam('filter'))), $data);
        if (isset($data["assignation_warehouse"])) {
            $collection->addFieldToFilter('assignation_warehouse', array('eq' => $data["assignation_warehouse"]));
            echo "<script type='text/javascript'>
				setTimeout(function(){\$('sales_order_grid_filter_assignation_warehouse').value='" . $data["assignation_warehouse"] . "'},10);
			</script>";
        }

        if ($all)
            return $collection;
        foreach ($pos as $p) {
            $filters[] = array('finset' => $p);
        }
        if (!count($pos))
            $filters[] = array('finset' => "No permissions!");


        $collection->addFieldToFilter('assignation_warehouse', $filters);


        return $collection;
    }

    public function saveConfig($observer) {




        $date = Mage::getStoreConfig("advancedinventory/setting/order_notification_from_date");
        $date_config = substr($date, 6, 4) . '-' . substr($date, 0, 2) . '-' . substr($date, 3, 2);

        $orders = Mage::getModel("sales/order")->getCollection()->addFieldToFilter('created_at', array('lt' => $date_config));
        foreach ($orders as $order) {
            $order->setAssignationWarehouse('-1')->save();
        }
        $orders = Mage::getModel("sales/order")->getCollection()
                ->addFieldToFilter('created_at', array('gteq' => $date_config))
                ->addFieldToFilter('assignation_warehouse', array('eq' => '-1'));
        foreach ($orders as $order) {
            $order->setAssignationWarehouse('0')->save();
        }
    }

    public function saveInventory($observer) {
        Mage::getConfig()->saveConfig("cataloginventory/options/can_subtract", 0, "default", "0");
        Mage::getSingleton('adminhtml/session')->addWarning(Mage::helper('advancedinventory')->__('Advanced Inventory notice : `Decrease Stock When Order is Placed` must be disabled.'));
    }

    function productSave($observer) {

        $product = $observer->getProduct();
        $productData = $observer->getRequest()->getParams();
        if (!isset($productData["product"]["stock_data"]))
            $productData = $observer->getRequest()->getPost();
        $product_id = $product->getId();
        $stockitem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
        $store_id = $this->_getStore()->getStoreId();
        $permissions = Mage::helper('advancedinventory/permissions')->getUserPermissions();
        $is_admin = $permissions->isAdmin();


        if (!in_array($product->getTypeId(), array("configurable", "bundle", "grouped"))) {

            if ($store_id || !$is_admin) {

                $multistock_enabled = Mage::getModel("advancedinventory/stock")->getMultiStockEnabledByProductId($product_id);
                foreach (Mage::getModel("advancedinventory/stock")->getStocksByProductIdAndStoreId($product_id) as $store) {
                    $use_config_setting_for_backorders = ($store->getUse_config_setting_for_backorders()) ? "on" : null;
                    $inventory[$product_id]['pos_wh'][$store->getPlaceId()] = array(
                        "manage_stock" => $store->getManage_stock(),
                        "qty" => $store->getQty(),
                        "backorder_allowed" => $store->getBackorder_allowed(),
                        "use_config_setting_for_backorders" => $use_config_setting_for_backorders
                    );
                }

                $temp_inventory = $observer->getRequest()->getParam("inventory");
                foreach ($temp_inventory[$product_id]['pos_wh'] as $pos_id => $invData) {

                    $inventory[$product_id]['pos_wh'][$pos_id] = $invData;
                }
            } else {
                if (isset($productData["inventory"]))
                    $multistock_enabled = $productData["inventory"][$product_id]['multi_stock_enabled'];
                else
                    $multistock_enabled = false;
                $inventory = $observer->getRequest()->getParam("inventory");
            }

            $updatedData['stock_data'] = $productData["product"]["stock_data"];

            if ($multistock_enabled) {

                $pos_wh = $inventory[$product_id]['pos_wh'];
                $total_qty = 0;

                foreach ($pos_wh as $stock) {
                    if ($stock["manage_stock"])
                        $total_qty+=$stock["qty"];
                }

                $updatedData['stock_data']['qty'] = $total_qty;
                $updatedData['stock_data']['backorders'] = 1;
                $updatedData['stock_data']['use_config_backorders'] = 0;


                $stock = Mage::getModel("advancedinventory/item")->loadByProductId($product_id);

                $data = array(
                    "id" => $stock->getId(),
                    "product_id" => $product_id,
                    "manage_local_stock" => true,
                );
                if ($stock->getManage_local_stock() != $multistock_enabled) {
                    Mage::helper('advancedinventory/log')->insertRow("Product page", "Status updated", "P#$product_id", "Enable multi-stock");
                }
                $stock_id = $stock->setData($data)->save()->getId();


                $model = Mage::getModel("advancedinventory/stock");
                $is_in_stock = false;
                foreach ($inventory[$product_id]['pos_wh'] as $pos_id => $invData) {

                    foreach ($invData as $key => $value)
                        $$key = $value;

                    $use_config_setting_for_backorders = $use_config_setting_for_backorders == 'on' ? '1' : '0';
                    $use_config_setting_for_backorders ? $backorder_allowed = null : null;

                    if (!$manage_stock) {
                        $qty = 0;
                        $backorder_allowed = null;
                        $use_config_setting_for_backorders = '1';
                    }

                    $stock = $model->getStockByProductIdAndPlaceId($product_id, $pos_id);

                    $data = array(
                        "id" => $stock->getId(),
                        "localstock_id" => $stock_id,
                        "product_id" => $product_id,
                        "place_id" => $pos_id,
                        "manage_stock" => $manage_stock,
                        "quantity_in_stock" => $qty,
                        "backorder_allowed" => $backorder_allowed,
                        "use_config_setting_for_backorders" => $use_config_setting_for_backorders,
                    );
                    if (!$manage_stock || $backorder_allowed > 0 || ($use_config_setting_for_backorders && Mage::getStoreConfig("cataloginventory/item_options/backorders")))
                        $is_in_stock = true;

                    if ($stock->getQuantity_in_stock() != $qty || $stock->getUse_config_setting_for_backorders() != $use_config_setting_for_backorders || $stock->getManageStock() != $manage_stock || $stock->getBackorder_allowed() != $backorder_allowed) {
                        Mage::helper('advancedinventory/log')->insertRow("Product page", "Qty updated", "P#$product_id,W#$pos_id", "Qty : " . (int) $stock->getQuantity_in_stock() . " -> " . $qty . ", Manage stock = " . (int) $manage_stock . ", Backorder = " . $backorder_allowed . ", Use config setting = " . $use_config_setting_for_backorders);
                        $model->setData($data)->save();
                    }
                }
            } else {
                $total_qty = $productData['product']['stock_data']['qty'];
                $updatedData['stock_data']['qty'] = $total_qty;
                $stock = Mage::getModel("advancedinventory/item")->loadByProductId($product_id);

                if ((int) $stock->getManageLocalStock() != (int) $multistock_enabled) {
                    Mage::helper('advancedinventory/log')->insertRow("Product page", "Status updated", "P#$product_id", "Disable multi-stock");
                }

                $stock->delete();
            }


            if (Mage::getStoreConfig("advancedinventory/setting/auto_update_stock_status")) {

                if ($multistock_enabled) {
                    $minQty = ($stockitem->getUseConfigMaxSaleQty()) ? Mage::getStoreConfig("cataloginventory/item_options/min_qty") : $stockitem->getMinSaleQty();
                    if ($total_qty > $minQty || $is_in_stock) {
                        $updatedData['stock_data']['is_in_stock'] = 1;
                    } else {
                        $updatedData['stock_data']['is_in_stock'] = 0;
                    }
                    $stockStatus = ($updatedData['stock_data']['is_in_stock'] == 1) ? true : false;
                }
            } else {
                $permissions = Mage::helper('advancedinventory/permissions')->getUserPermissions();
                $all = $permissions->isAdmin();
                if ($all)
                    $stockStatus = ($productData['product']['stock_data']['is_in_stock'] == 1) ? true : false;
                else
                    $stockStatus = $stockitem->getIsInStock();
                $updatedData['stock_data']['is_in_stock'] = $stockStatus;
            }


            if ((int) $stockitem->getQty() != $total_qty) {
                Mage::helper('advancedinventory/log')->insertRow("Product page", "Total Qty updated", "P#$product_id", "Qty : " . (int) $stockitem->getQty() . " -> " . $total_qty);
            }

            if ($stockStatus != $stockitem->getIsInStock()) {
                $status = ($stockStatus) ? "In stock" : "Out of stock";
                Mage::helper('advancedinventory/log')->insertRow("Product page", "Status updated", "P#$product_id", $status);
            }


            $product->addData($updatedData);
        }
        return $observer;
    }

}
