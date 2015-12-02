<?php

class Wyomind_Advancedinventory_Model_CatalogInventory_Stock_Item extends Mage_CatalogInventory_Model_Stock_Item {

    private $_backorders = 0;

    public function checkQty($qty) {

        $productQty = $qty;
        if (Mage::getStoreConfig("advancedinventory/setting/usemultistock") && Mage::app()->getStore()->getStoreId() > 0) {


            $productId = $this->getProductId();


            if (Mage::app()->getStore()->isAdmin())
                $stores = Mage::getModel('pointofsale/pointofsale')->getPlaces();
            else
                $stores = Mage::getModel('pointofsale/pointofsale')->getPlacesByStoreId(Mage::app()->getStore()->getStoreId());

            $qty = 0;
            $backOrderAllowed = 0;
            $useDefaultConfigBackOrder = 1;
            $defaultBackOrdeValue = Mage::getStoreConfig("cataloginventory/item_options/backorders");
            $manageLocalStock = 0;
            $noStock = 0;
            foreach ($stores as $s) {
                $stock = Mage::getModel('advancedinventory/stock')->getStockByProductIdAndPlaceId($productId, $s['place_id']);
                $qty += $stock->getQuantity_in_stock();
                if (!$stock->getManageStock())
                    $noStock++;
                if ($stock->getBackorder_allowed() > $backOrderAllowed)
                    $backOrderAllowed = $stock->getBackorder_allowed();
                if (!$stock->getUse_config_setting_for_backorders())
                    $useDefaultConfigBackOrder = $stock->getUse_config_setting_for_backorders();
                $manageLocalStock += $stock->getManage_local_stock();
            }

            $rtn = true;


            if ($manageLocalStock) {
                if ($noStock) {
                    $rtn = true;
                } else if ($qty - $productQty >= 0)
                    $rtn = true;
                else if ($qty - $productQty < 0 && $useDefaultConfigBackOrder && $defaultBackOrdeValue)
                    $rtn = true;
                else if ($qty - $productQty < 0 && !$useDefaultConfigBackOrder && $backOrderAllowed)
                    $rtn = true;
                else
                    $rtn = false;

                return $rtn;
            }
            else {
                return $this->checkQtyFinal($productQty);
            }
        } else {

            return $this->checkQtyFinal($productQty);
        }
    }

    public function checkQtyFinal($productQty) {

        if (!$this->getManageStock() || Mage::app()->getStore()->isAdmin()) {
            return true;
        }

        if ($this->getQty() - $productQty < 0) {

            switch ($this->getBackorders()) {
                case Mage_CatalogInventory_Model_Stock::BACKORDERS_YES_NONOTIFY:
                case Mage_CatalogInventory_Model_Stock::BACKORDERS_YES_NOTIFY:
                    break;
                default:
                    return false;
                    break;
            }
        }

        return true;
    }

    public function checkQuoteItemQty($qty, $summaryQty, $origQty = 0) {

        $result = new Varien_Object();
        $result->setHasError(false);

        if (!is_numeric($qty)) {
            $qty = Mage::app()->getLocale()->getNumber($qty);
        }

        /**
         * Check quantity type
         */
        $result->setItemIsQtyDecimal($this->getIsQtyDecimal());

        if (!$this->getIsQtyDecimal()) {
            $result->setHasQtyOptionUpdate(true);
            $qty = intval($qty);

            /**
             * Adding stock data to quote item
             */
            $result->setItemQty($qty);

            if (!is_numeric($qty)) {
                $qty = Mage::app()->getLocale()->getNumber($qty);
            }
            $origQty = intval($origQty);
            $result->setOrigQty($origQty);
        }

        if ($this->getMinSaleQty() && $qty < $this->getMinSaleQty()) {
            $result->setHasError(true)
                    ->setMessage(
                            Mage::helper('cataloginventory')->__('The minimum quantity allowed for purchase is %s.', $this->getMinSaleQty() * 1)
                    )
                    ->setErrorCode('qty_min')
                    ->setQuoteMessage(Mage::helper('cataloginventory')->__('Some of the products cannot be ordered in requested quantity.'))
                    ->setQuoteMessageIndex('qty');
            return $result;
        }

        if ($this->getMaxSaleQty() && $qty > $this->getMaxSaleQty()) {
            $result->setHasError(true)
                    ->setMessage(
                            Mage::helper('cataloginventory')->__('The maximum quantity allowed for purchase is %s.', $this->getMaxSaleQty() * 1)
                    )
                    ->setErrorCode('qty_max')
                    ->setQuoteMessage(Mage::helper('cataloginventory')->__('Some of the products cannot be ordered in requested quantity.'))
                    ->setQuoteMessageIndex('qty');
            return $result;
        }

        $result->addData($this->checkQtyIncrements($qty)->getData());
        if ($result->getHasError()) {
            return $result;
        }

        if (!$this->getManageStock()) {
            return $result;
        }

        if (!$this->getIsInStock()) {

            $result->setHasError(true)
                    ->setMessage(Mage::helper('cataloginventory')->__('This product is currently out of stock.'))
                    ->setQuoteMessage(Mage::helper('cataloginventory')->__('Some of the products are currently out of stock'))
                    ->setQuoteMessageIndex('stock');
            $result->setItemUseOldQty(true);
            return $result;
        }

        if (!$this->checkQty($summaryQty) || !$this->checkQty($qty)) {
            $message = Mage::helper('cataloginventory')->__('The requested quantity for "%s" is not available.', $this->getProductName());
            $result->setHasError(true)
                    ->setMessage($message)
                    ->setQuoteMessage($message)
                    ->setQuoteMessageIndex('qty');
            return $result;
        } else {

            if (Mage::getStoreConfig("advancedinventory/setting/usemultistock")) {

                $productId = $this->getProductId();
                if (Mage::app()->getStore()->isAdmin())
                    $stores = Mage::getModel('pointofsale/pointofsale')->getPlaces();
                else
                    $stores = Mage::getModel('pointofsale/pointofsale')->getPlacesByStoreId(Mage::app()->getStore()->getStoreId());


                $_qty = 0;
                $backOrderAllowed = 0;
                $useDefaultConfigBackOrder = 1;
                $defaultBackOrdeValue = Mage::getStoreConfig("cataloginventory/item_options/backorders");
                $manageLocalStock = 0;
                $noStock = 0;
                foreach ($stores as $s) {
                    $stock = Mage::getModel('advancedinventory/stock')->getStockByProductIdAndPlaceId($productId, $s['place_id']);
                    $_qty += $stock->getQuantity_in_stock();
                    if (!$stock->getManageStock())
                        $noStock++;
                    if ($stock->getBackorder_allowed() > $backOrderAllowed)
                        $backOrderAllowed = $stock->getBackorder_allowed();
                    if (!$stock->getUse_config_setting_for_backorders())
                        $useDefaultConfigBackOrder = $stock->getUse_config_setting_for_backorders();
                    $manageLocalStock += $stock->getManage_local_stock();
                }


                if ($manageLocalStock) {
                    $this->setQty(number_format($_qty, 4));
                    if ($noStock) {
                        $this->_backorders = 1;
                    } else if ($useDefaultConfigBackOrder && $defaultBackOrdeValue) {
                        $this->_backorders = $defaultBackOrdeValue;
                    } else if (!$useDefaultConfigBackOrder && $backOrderAllowed) {
                        $this->_backorders = $backOrderAllowed;
                    } else
                        $this->_backorders = $this->getBackorders();
                } else
                    $this->_backorders = $this->getBackorders();
            } else
                $this->_backorders = $this->getBackorders();


            if (($this->getQty() - $summaryQty) < 0) {

                if ($this->getProductName()) {

                    if ($this->getIsChildItem()) {

                        $backorderQty = ($this->getQty() > 0) ? ($summaryQty - $this->getQty()) * 1 : $qty * 1;
                        if ($backorderQty > $qty) {
                            $backorderQty = $qty;
                        }

                        $result->setItemBackorders($backorderQty);
                    } else {

                        $orderedItems = $this->getOrderedItems();
                        $itemsLeft = ($this->getQty() > $orderedItems) ? ($this->getQty() - $orderedItems) * 1 : 0;

                        $backorderQty = ($itemsLeft > 0) ? ($qty - $itemsLeft) * 1 : $qty * 1;

                        if ($backorderQty > 0) {
                            $result->setItemBackorders($backorderQty);
                        }
                        $this->setOrderedItems($orderedItems + $qty);
                    }


                    if ($this->_backorders == Mage_CatalogInventory_Model_Stock::BACKORDERS_YES_NOTIFY) {


                        if (!$this->getIsChildItem()) {
                            $result->setMessage(
                                    Mage::helper('cataloginventory')->__('This product is not available in the requested quantity. %s of the items will be backordered.', ($backorderQty * 1))
                            );
                        } else {
                            $result->setMessage(
                                    Mage::helper('cataloginventory')->__('"%s" is not available in the requested quantity. %s of the items will be backordered.', $this->getProductName(), ($backorderQty * 1))
                            );
                        }
                    } elseif (Mage::app()->getStore()->isAdmin()) {
                        $result->setMessage(
                                Mage::helper('cataloginventory')->__('The requested quantity for "%s" is not available.', $this->getProductName())
                        );
                    }
                }
            } else {
                if (!$this->getIsChildItem()) {
                    $this->setOrderedItems($qty + (int) $this->getOrderedItems());
                }
            }
        }

        return $result;
    }

}
