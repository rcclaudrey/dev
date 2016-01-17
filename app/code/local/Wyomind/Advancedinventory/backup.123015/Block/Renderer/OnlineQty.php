<?php

class Wyomind_Advancedinventory_Block_Renderer_OnlineQty extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {
        if (in_array($row->getTypeId(), array('simple', 'virtual', 'downloadable'))) {
            if (Mage::getStoreConfig("advancedinventory/setting/lock_online_stock") && $row->getManageLocalStock()) {
                $html = "<span id='foo_online_stock_qty_" . $row->getId() . "'>" . number_format($row->getOnlineQty(), 0, '', '') . "</span><input id='online_stock_qty_" . $row->getId() . "'  class='keydown online_stock_qty' type='text' productid='" . $row->getId() . "' style='display:none;text-align:center; width:50px;' name='inventory[" . $row->getId() . "][online_stock_qty]' value='" . number_format($row->getOnlineQty(), 0, '', '') . "' onchange='InventoryManager.updateStocks(" . $row->getId() . ",false)'/>";
            } else {
                $html = "<span style='display:none;' id='foo_online_stock_qty_" . $row->getId() . "'>" . number_format($row->getOnlineQty(), 0, '', '') . "</span><input id='online_stock_qty_" . $row->getId() . "'  class='keydown online_stock_qty' type='text' productid='" . $row->getId() . "' style='text-align:center; width:50px;' name='inventory[" . $row->getId() . "][online_stock_qty]' value='" . number_format($row->getOnlineQty(), 0, '', '') . "' onchange='InventoryManager.updateStocks(" . $row->getId() . ",false)'/>";
            }
            return "<input type='hidden' value='" . $row->getStockId() . "' id='inventory_" . $row->getId() . "_local_stock_id' name='inventory[" . $row->getId() . "][local_stock_id]' / >" . $html;
        }
        else
            return "0";
    }

}
