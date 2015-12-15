<?php

class Wyomind_Advancedinventory_Helper_Data extends Mage_Core_Helper_Abstract {

    public $log;

    public function getStockStatus($stock) {

        $qty = $stock->getQuantity_in_stock();

        $pos_managestock = $stock->getManageStock();
        $backOrderAllowed = $stock->getBackorder_allowed();
        $useDefaultConfigBackOrder = $stock->getUse_config_setting_for_backorders();
        $manageLocalStock = $stock->getManage_local_stock();
        $defaultBackOrdeValue = Mage::getStoreConfig("cataloginventory/item_options/backorders");


        if ($manageLocalStock) {
            if (!$pos_managestock)
                $rtn = "not_managed";

            elseif ($useDefaultConfigBackOrder && $defaultBackOrdeValue) {
                if ($qty > 0 || $backOrderAllowed < 2)
                    $rtn = 'in_stock';
                else {
                    $rtn = ' backorder';
                }
            } elseif (!$useDefaultConfigBackOrder && $backOrderAllowed) {
                if ($qty > 0 || $backOrderAllowed < 2)
                    $rtn = 'in_stock';
                else {
                    $rtn = ' backorder';
                }
            } elseif ($qty > 0)
                $rtn = 'in_stock';
            else {

                $rtn = 'out_of_stock';
            }
        } else {
            $rtn = 'multistock_disabled';
        }
        return $rtn;
    }

    public function isBackorderable($stock) {


        $defaultBackOrdeValue = Mage::getStoreConfig("cataloginventory/item_options/backorders");

        $pos_managestock = $stock->getManageStock();
        $backOrderAllowed = $stock->getBackorder_allowed();

        $useDefaultConfigBackOrder = $stock->getUse_config_setting_for_backorders();
        $manageLocalStock = $stock->getManage_local_stock();
        $defaultBackOrdeValue = Mage::getStoreConfig("cataloginventory/item_options/backorders");



        if ($manageLocalStock) {
            if (!$pos_managestock)
                return false;
            elseif ($useDefaultConfigBackOrder && $defaultBackOrdeValue) {
                return true;
            } elseif (!$useDefaultConfigBackOrder && $backOrderAllowed) {
                return true;
            } else {
                return false;
            }
        }
    }

    protected function getRules($stringrules) {
        return explode("\n", $stringrules);
    }

    protected function addressMatch($address_filter, $address) {
        $excluding = false;
        $address_filter = trim($address_filter);
        $address_filter = str_replace(
                array('\(', '\)', '\,'), array('__opening_parenthesis__', '__closing_parenthesis__', '__comma__'), $address_filter
        );
        if ($address_filter == '*') {
            $this->log .='      country code ' . $address['country_code'] . ' matches' . "\r\n";
            return true;
        }
        if (preg_match('#\* *- *\((.*)\)#s', $address_filter, $result)) {
            $address_filter = $result[1];
            $excluding = true;
        }
        $tmp_address_filter_array = explode(',', trim($address_filter));
        $concat = false;
        $concatened = '';
        $address_filter_array = array();
        $i = 0;
        foreach ($tmp_address_filter_array as $address_filter) {
            if ($concat)
                $concatened .= ',' . $address_filter;
            else {
                if ($i < count($tmp_address_filter_array) - 1 && preg_match('#\(#', $address_filter)) {
                    $concat = true;
                    $concatened .= $address_filter;
                } else
                    $address_filter_array[] = $address_filter;
            }
            if (preg_match('#\)#', $address_filter)) {
                $address_filter_array[] = $concatened;
                $concatened = '';
                $concat = false;
            }
            $i++;
        }
        foreach ($address_filter_array as $address_filter) {
            $address_filter = trim($address_filter);
            if (preg_match('#([A-Z]{2}) *(-)? *(?:\( *(-)? *(.*)\))?#s', $address_filter, $result)) {
                $country_code = $result[1];
                if ($address['country_code'] == $country_code) {
                    $this->log .='      country code ' . $address['country_code'] . ' matches' . "\r\n";
                    if (!isset($result[4]) || $result[4] == '')
                        return !$excluding;
                    else {
                        $region_codes = explode(',', $result[4]);
                        $in_array = false;
                        for ($i = count($region_codes); --$i >= 0;
                        ) {
                            $code = trim(str_replace(
                                            array('__opening_parenthesis__', '__closing_parenthesis__', '__comma__'), array('(', ')', ','), $region_codes[$i]
                            ));
                            $region_codes[$i] = $code;
                            if ($address['region_code'] === $code) {
                                $this->log .='      region code ' . $address['region_code'] . ' matches' . "\r\n";
                                $in_array = true;
                            } else if ($address['postcode'] === $code) {
                                $this->log .='      postcode ' . $address['postcode'] . ' matches' . "\r\n";
                                $in_array = true;
                            } else if (mb_substr($code, 0, 1) == '/' && mb_substr($code, mb_strlen($code) - 1, 1) == '/' && @preg_match($code, $address['postcode'])) {
                                $this->log .='      postcode ' . $address['postcode'] . ' matches ' . htmlentities($code) . "\r\n";
                                $in_array = true;
                            } else if (strpos($code, '*') !== false && preg_match('/^' . str_replace('*', '(?:.*)', $code) . '$/', $address['postcode'])) {
                                $this->log .='      postcode ' . $address['postcode'] . ' matches ' . htmlentities($code) . "\r\n";
                                $in_array = true;
                            }
                            if ($in_array)
                                break;
                        }
                        if (!$in_array) {
                            $this->log .='      region code ' . $address['region_code'] . ' and postcode' . $address['postcode'] . ' don\'t match' . "\r\n";
                        }
// Vérification stricte
                        $excluding_region = $result[2] == '-' || $result[3] == '-';
                        if ($excluding_region && !$in_array || !$excluding_region && $in_array)
                            return !$excluding;
                    }
                } else
                    $this->log .='      country code ' . $address['country_code'] . ' doesn\'t matches' . "\r\n";
            }
        }
        return $excluding;
    }

    public $debug = false;

    public function getOrderedItems($order) {
        $orderedItems = array();
        $items = $order->getAllItems();
        $i = 0;
        foreach ($items as $item) {

            if ($item->getQtyOrdered() > 0 && in_array($item->getProductType(), array('simple', 'virtual', 'downloadable', 'grouped'))) {
                $orderedItems[$item->getProductId()]['sku'] = $item->getSku();

                if ($item->getParentItemId() != null && $items[$i - 1]->getProductType() == "configurable")
                    $orderedItems[$item->getProductId()]['qty'] = $item->getQtyOrdered() - $items[$i - 1]->getOrigData('qty_refunded') - $items[$i - 1]->getQtyCanceled();
                else {

                    $orderedItems[$item->getProductId()]['qty'] = $item->getQtyOrdered() - $item->getQtyRefunded() - $item->getQtyCanceled();
                }

                $orderedItems[$item->getProductId()]['id'] = $item->getProductId();
                $orderedItems[$item->getProductId()]['name'] = $item->getName();
            }
            $i++;
        }

        return $orderedItems;
    }

    function getAllowedOrder($order) {
        $disallowed = explode(',', Mage::getStoreConfig("advancedinventory/setting/disallow_assignation_status"));
        return !in_array($order->getStatus(), $disallowed);
    }

    public function getAssignationRules($method, $rules) {
        switch ($method) {
            case 0:
                return null;
                break;
            case 1:
                return '*';
                break;
            case 2:
                return $rules;
                break;
        }
    }

    public function getAssignation($order, $orderedItems) {

        $this->log = "\r\n-----------------------------------------------------------------------------\r\n";
        $this->log .= "------------Start assignation process for order #" . $order->getIncrementId() . " ------------------\r\n";
        $this->log .= "-----------------------------------------------------------------------------\r\n\r\n";

        $this->log .= "Shipping method : " . $order->getShippingMethod() . "\r\n\r\n";
        $this->assignation_warehouses = array();
        $this->assignation_stock = array();

        if (version_compare(Mage::getVersion(), '1.3.0', '>')) {
            if ($order->getShippingAddress()) {
                $shippingId = $order->getShippingAddress()->getId();
                $address = Mage::getModel('sales/order_address')->load($shippingId);
            } else {
                $address = null;
            }
        } else
            $address = $order->getShippingAddress();

        if (stripos($order->getShippingMethod(), 'pickupatstore') !== FALSE) {
            $assignation = substr($order->getShippingMethod(), stripos($order->getShippingMethod(), '_') + 1);
            $this->log .= "* * * * * Assign to warehouse ID : " . $assignation . "\r\n\r\n";
            Mage::log($this->log, NULL, "Inventory_assignation.log");
            $this->assignation_warehouses = array($assignation);
            foreach ($orderedItems as $item) {
                $this->assignation_stock[$item['id']][$assignation] = $item['qty'];
            }
            $this->decreaseStock($orderedItems, $this->assignation_stock);
        } else if ($address == null) {
            $this->log .= "* * * * * Not Assigned (no shipping address) \r\n\r\n";
            Mage::log($this->log, NULL, "Inventory_assignation.log");
            $this->assignation_warehouses = array(0);
        } else {

            $directory = Mage::getModel('directory/region');
            $destination['country_code'] = $address->getCountryId();
            $destination['region_code'] = $directory->load($address->getRegionId())->getCode();
            $destination['postcode'] = $address->getPostcode();

            $this->log .= "Shipped to : " . $destination['country_code'] . ',' . $destination['region_code'] . ',' . $destination['postcode'] . "\r\n\r\n";

            $places = Mage::getModel('pointofsale/pointofsale')->getPlacesByStoreId($order->getStoreId());

            if (Mage::getStoreConfig("advancedinventory/setting/multiple_assignation_enabled")) {

                foreach ($orderedItems as $item) {
                    $this->log .= "\r\n- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -\r\n"
                            . "* Checking availability for : " . $item['sku'] . "[ID:" . $item["id"] . "], Ordered Qty : " . $item["qty"] . "\r\n";
                    if (Mage::getModel('advancedinventory/stock')->getMultiStockEnabledByProductId($item["id"])) {
                        foreach ($places as $place) {
                            $this->log .= ". . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . .\r\n";
                            $this->log .= "* * Checking warehouse : " . $place->getName() . " [" . $place->getStoreCode() . "]\r\n";
                            $rules = $this->getAssignationRules($place->getUseAssignationRules(), $place->getInventoryAssignationRules());
                            foreach ($this->getRules($rules) as $rule) {
                                $this->log .= "* * * Checking rule '" . trim($rule) . "' \r\n";
                                if ($rule == '*' || $this->addressMatch($rule, $destination)) {
                                    $this->log .="* * * * This rule macth!\r\n";
                                    $available = $this->checkProductAvailability($item, $place, true, false, $places);
                                    if ($available >= 1) {
                                        $this->log .= "* * * * * * * Assign to warehouse ID : " . $place->getPlaceId() . "\r\n\r\n";
                                        //  Mage::log($this->log, NULL, "Inventory_assignation.log");
                                    }
                                    if ($available == 2) {
                                        continue 3;
                                    }
                                } else {
                                    $this->log .= "* * * * This rule doesn't macth!\r\n";
                                }
                            }
                        }
                    } else {

                        $this->log .= "* * Multi-stock is not managed\r\n";
                        $inventory = Mage::getModel('cataloginventory/stock_item')->loadByProduct($item["id"]);

                        if (($inventory->getManageStock() && !$inventory->getUse_config_manage_stock()) || ($inventory->getUse_config_manage_stock() && Mage::getStoreConfig("cataloginventory/item_options/manage_stock"))) {
                            $new_qty = $inventory->getQty() - $item["qty"];
                            $this->log .= "* * * Stock decremented (Qty : $new_qty)\r\n";
                        } else {
                            $this->log .= "* * * Stock is not managed\r\n";
                        }
                    }
                }
            }else {

                foreach ($places as $place) {
                    $this->log .= "\r\n- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -\r\n";
                    $this->log .= "* Checking warehouse : " . $place->getName() . " [" . $place->getStoreCode() . "]\r\n";

                    foreach ($orderedItems as $item) {
                        if (Mage::getModel('advancedinventory/stock')->getMultiStockEnabledByProductId($item["id"])) {
                            $this->log .= ". . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . .\r\n";
                            $this->log .= "* * Checking rules for " . $item['sku'] . "[ID:" . $item["id"] . "], ordered qty :" . $item["qty"] ."\r\n";
                            $rules = $this->getAssignationRules($place->getUseAssignationRules(), $place->getInventoryAssignationRules());
                            foreach ($this->getRules($rules) as $rule) {
                                $this->log .= "* * * Checking rule '" . trim($rule) . "' \r\n";
                                if ($rule == '*' || $this->addressMatch($rule, $destination, false)) {
                                    $this->log .="* * * * This rule macth!\r\n";
								
                                    $available = $this->checkProductAvailability($item, $place, false, false, $places);
								     if ($available == 2) {
									    continue 2;
                                    } else {
                                        $this->assignation_warehouses = array();
                                        $this->assignation_stock = array();
                                        continue 3;
                                    }
                                } else {
                                    $this->log .= "* * * * This rule doesn't match!\r\n";
                                }
                            }
                        } else {

                            $this->log .= "* * Multi-stock is not managed\r\n";
                            $inventory = Mage::getModel('cataloginventory/stock_item')->loadByProduct($item["id"]);

                            if (($inventory->getManageStock() && !$inventory->getUse_config_manage_stock()) || ($inventory->getUse_config_manage_stock() && Mage::getStoreConfig("cataloginventory/item_options/manage_stock"))) {
                                $new_qty = $inventory->getQty() - $item["qty"];
                                $this->log .= "* * * Stock decremented (Qty : $new_qty)\r\n";
                            } else {
                                $this->log .= "* * * Stock is not managed\r\n";
                            }
                        }
                    }
                   
                }
            }
            $this->log .= "\r\n-----------------------------------------------------------------------------\r\n";
            $this->log .= "ASSIGNED TO : " . implode(",", $this->assignation_warehouses);
            $this->log .= "\r\n-----------------------------------------------------------------------------\r\n";
            $this->log .= "STOCK ASSIGNATION TO : " . json_encode($this->assignation_stock);
            $this->log .= "\r\n-----------------------------------------------------------------------------\r\n\r\n";
            Mage::log($this->log, NULL, "Inventory_assignation.log");



            if (Mage::app()->getStore()->isAdmin()) {
                $type = "Backend";
            } else {
                $type = "Frontend";
            }

            $this->decreaseStock($orderedItems, $this->assignation_stock, array("action" => "Order placed", "type" => $type, "store_id" => $order->getStoreId(), "order_id" => $order->getId(), "warehouses" => $this->assignation_warehouses));
        }
       
        return $this;
    }

    public function checkProductAvailability($item, $place, $mutiple_assignation = true, $pickup = false, $places) {

        $ordered_qty = 0;

        $productId = $item['id'];

        if ($productId != '') {

            $stock = Mage::getModel('advancedinventory/stock')->getStockByProductIdAndPlaceId($productId, $place->getPlaceId());

            $assigned_qty = 0;
            if (!$pickup)
                foreach ($this->assignation_stock[$item['id']] as $q) {
                    $assigned_qty+=$q;
                }
            $ordered_qty = $item['qty'] - $assigned_qty;
            //if (!$ordered_qty)
            //  continue;
            $qty_in_stock = $stock->getQuantity_in_stock();
            $this->log .= "* * * * * Checking availability, " . $assigned_qty . " already assigned" . ", " . $qty_in_stock . " in stock \r\n";

            $qty = $qty_in_stock - $ordered_qty;

            $backOrderAllowed = $stock->getBackorder_allowed();
            $useDefaultConfigBackOrder = $stock->getUse_config_setting_for_backorders();
            $manageLocalStock = $stock->getManage_local_stock();
            $manageStock = $stock->getManageStock();
            $defaultBackOrdeValue = Mage::getStoreConfig("cataloginventory/item_options/backorders");
            $rtn = 2;
            $this->log .= "* * * * * * ";
            if ($manageLocalStock) {
                if (!$manageStock) {
                    $this->log .= "Stock management disabled!\r\n";
                    $this->assignation_stock[$item['id']][$place->getPlaceId()] = 0;
                    $rtn = 0;
                } elseif ($qty >= 0) {
                    $this->log .= "Qty is available!\r\n";

                    if (trim($place->getGroup()) != '') {
                        foreach ($places as $p) {
                            if ($p->getGroup() == $place->getGroup()) {
                                $this->assignation_stock[$item['id']][$p->getPlaceId()] = $ordered_qty;
                                $this->assignation_warehouses[] = $p->getPlaceId();
                            }
                        }
                    } else {
                        $this->assignation_stock[$item['id']][$place->getPlaceId()] = $ordered_qty;
                        $this->assignation_warehouses[] = $place->getPlaceId();
                    }

                    $rtn = 2;
                } elseif ($qty <= 0 && $useDefaultConfigBackOrder && $defaultBackOrdeValue) {
                    $this->log .= "Backorder allowed!\r\n";
                    if (trim($place->getGroup()) != '') {
                        foreach ($places as $p) {
                            if ($p->getGroup() == $place->getGroup()) {
                                $this->assignation_stock[$item['id']][$p->getPlaceId()] = $ordered_qty;
                                $this->assignation_warehouses[] = $p->getPlaceId();
                            }
                        }
                    } else {
                        $this->assignation_stock[$item['id']][$place->getPlaceId()] = $ordered_qty;
                        $this->assignation_warehouses[] = $place->getPlaceId();
                    }
                    $rtn = 2;
                } elseif ($qty <= 0 && !$useDefaultConfigBackOrder && $backOrderAllowed) {
                    $this->log .= "Backorder allowed!\r\n";
                    if (trim($place->getGroup()) != '') {
                        foreach ($places as $p) {
                            if ($p->getGroup() == $place->getGroup()) {
                                $this->assignation_stock[$item['id']][$p->getPlaceId()] = $ordered_qty;
                                $this->assignation_warehouses[] = $p->getPlaceId();
                            }
                        }
                    } else {
                        $this->assignation_stock[$item['id']][$place->getPlaceId()] = $ordered_qty;
                        $this->assignation_warehouses[] = $place->getPlaceId();
                    }
                    $rtn = 2;
                } elseif ($qty_in_stock > 0 && $qty < 0) {
                    if ($mutiple_assignation) {
                        $this->log .= "Qty is partially available!\r\n";

                        if (trim($place->getGroup()) != '') {
                            foreach ($places as $p) {
                                if ($p->getGroup() == $place->getGroup()) {
                                    $this->assignation_stock[$item['id']][$p->getPlaceId()] = $qty_in_stock;
                                    $this->assignation_warehouses[] = $p->getPlaceId();
                                }
                            }
                        } else {
                            $this->assignation_stock[$item['id']][$place->getPlaceId()] = $qty_in_stock;
                            $this->assignation_warehouses[] = $place->getPlaceId();
                        }

                        $rtn = 1;
                    } else {
                        $this->log .= "Qty is not completely available!\r\n";
                        if (trim($place->getGroup()) != '') {
                            foreach ($places as $p) {
                                if ($p->getGroup() == $place->getGroup()) {
                                    $this->assignation_stock[$item['id']][$p->getPlaceId()] = 0;
                                }
                            }
                        } else {
                            $this->assignation_stock[$item['id']][$place->getPlaceId()] = 0;
                        }
                        $rtn = 0;
                    }
                } else {
                    $this->log .= "Qty is not available!\r\n";
                    if (trim($place->getGroup()) != '') {
                        foreach ($places as $p) {
                            if ($p->getGroup() == $place->getGroup()) {
                                $this->assignation_stock[$item['id']][$p->getPlaceId()] = 0;
                            }
                        }
                    } else {
                        $this->assignation_stock[$item['id']][$place->getPlaceId()] = 0;
                    }
                    $rtn = 0;
                }
            }
        }

        return $rtn;
    }

    public function decreaseStock($orderedItems, $assignation = array(), $additional = array(), $sign = 1) {

        Mage::helper('advancedinventory/log')->insertRow($additional["type"] . " order", $additional['action'], "S#" . $additional["store_id"] . ",O#" . $additional["order_id"], "Assigned to : " . implode(", ", $additional["warehouses"]));

       

            foreach ($orderedItems as $orderedItem) {

                try {

                    $stock = Mage::getModel('advancedinventory/stock')->getStocksByProductIdAndStoreId($orderedItem['id'])->getFirstItem();
                    if ($stock->getManageLocalStock()) {
                        $total_qty = 0;

                        foreach ($assignation[$orderedItem['id']] as $wh_id => $qty) {

                            $model = Mage::getModel('advancedinventory/stock')->getStockByProductIdAndPlaceId($orderedItem['id'], $wh_id);
                            if ($model->getManageStock()) {
                                $model_data = array(
                                    'quantity_in_stock' => $model->getQuantityInStock() - $sign * $qty,
                                    'product_id' => $orderedItem['id'],
                                    'place_id' => $wh_id,
                                    'localstock_id' => $stock->getStockProductId(),
                                    "id" => $model->getId()
                                );

                                if ($qty != 0) {

                                    Mage::helper('advancedinventory/log')->insertRow($additional["type"] . " order", "Assignation updated", "S#" . $additional["store_id"] . ",O#" . $additional["order_id"] . ",P#" . $orderedItem['id'] . ",W#$wh_id", "Assigned qty : $qty ");
                                    Mage::helper('advancedinventory/log')->insertRow($additional["type"] . " order", "Qty updated", "S#" . $additional["store_id"] . ",O#" . $additional["order_id"] . ",P#" . $orderedItem['id'] . ",W#$wh_id", "Qty : " . $model->getQuantityInStock() . " -> " . ($model->getQuantityInStock() - $sign * $qty));
                                }

                                $model->setData($model_data)->save();

                                $total_qty+=$qty;
                            }
                        }
                        $inventory = Mage::getModel('cataloginventory/stock_item')->loadByProduct($orderedItem['id']);

                        $new_qty = $inventory->getQty() - $sign * $total_qty;
                        if ($new_qty != $inventory->getQty())
                            Mage::helper('advancedinventory/log')->insertRow($additional["type"] . " order", "Total qty updated", "S#" . $additional["store_id"] . ",O#" . $additional["order_id"] . ",P#" . $orderedItem['id'], "Qty : " . (int) $inventory->getQty() . " -> " . ($new_qty), $additional['user']);

                        $inventory->setQty($new_qty)->save();
                    } else {
                        $inventory = Mage::getModel('cataloginventory/stock_item')->loadByProduct($orderedItem['id']);
                        if (($inventory->getManageStock() && !$inventory->getUse_config_manage_stock()) || ($inventory->getUse_config_manage_stock() && Mage::getStoreConfig("cataloginventory/item_options/manage_stock"))) {
                            $new_qty = $inventory->getQty() - $sign * $orderedItem['qty'];
                            if ($new_qty != $inventory->getQty())
                                Mage::helper('advancedinventory/log')->insertRow($additional["type"] . "order", "Total qty updated", "S# : " . $additional["store_id"] . ",O#" . $additional["order_id"] . ",P#" . $orderedItem['id'], "Qty : " . (int) $inventory->getQty() . " -> " . ($new_qty), $additional['user']);

                            $inventory->setQty($new_qty)->save();
                        }
                    }
                } catch (Exception $exception) {
                    Mage::log('Advanced Inventory says :' . $exception->getMessage(), Zend_Log::ERR);
                }
            }
        
    }

    public function increaseStock($orderedItems, $assignation, $additional = array()) {

        $this->decreaseStock($orderedItems, $assignation, $additional, -1);
    }

    public function getPickupPlaces($places) {

        $orderedItems = array();

        if (Mage::app()->getStore()->isAdmin()) {
            $obj = Mage::getSingleton('adminhtml/session_quote');
        } else {
            $obj = Mage::getSingleton('checkout/session');
        }

        foreach ($obj->getQuote()->getAllItems() as $i => $item) {
            $orderedItems[$item->getItemId()]['sku'] = $item->getSku();
            if ($item->getParentItemId() == null)
                $orderedItems[$item->getItemId()]['qty'] = $item->getQty();
            else {
                $orderedItems[$item->getItemId()]['qty'] = $orderedItems[$item->getParentItemId()]['qty'];
                unset($orderedItems[$item->getParentItemId()]);
            }
            $orderedItems[$item->getItemId()]['id'] = $item->getProductId();
            $orderedItems[$item->getItemId()]['parent_item_id'] = $item->getParentItemId();
        }

        $this->log = "\r\n* Checking availability for quote #" . Mage::getSingleton('checkout/session')->getQuote()->getId() . "\r\n";


		$_places = array();
        foreach ($places as $place) {
            $this->log .= "\r\n- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -\r\n";
            $this->log .= "* Checking warehouse : " . $place->getName() . " [" . $place->getStoreCode() . "]\r\n";

            foreach ($orderedItems as $item) {
                $this->log .= ". . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . .\r\n";
                $this->log .= "* * Checking availability for : " . $item['sku'] . "[ID:" . $item["id"] . "], Ordered Qty : " . $item["qty"] . "\r\n";

                if (Mage::getModel('advancedinventory/stock')->getMultiStockEnabledByProductId($item["id"])) {
                    $available = $this->checkProductAvailability($item, $place, false, true);
                    if ($available < 2) {

                        $this->log .= "X X X X " . $place->getName() . " [" . $place->getStoreCode() . "] NOT added to the shipping methods\r\n\r\n";
                        continue 2;
                    }
                } else {
                    $this->log .= "* * Multi-stock is not managed, Available!\r\n";
                }
            }
            $this->log .= "V V V V " . $place->getName() . " [" . $place->getStoreCode() . "] added to the shipping methods\r\n\r\n";
            $_places[] = $place;
        }

        return $_places;
    }

}
