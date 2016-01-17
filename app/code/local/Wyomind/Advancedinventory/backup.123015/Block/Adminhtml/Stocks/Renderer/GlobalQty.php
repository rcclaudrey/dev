<?php

class Wyomind_Advancedinventory_Block_Adminhtml_Stocks_Renderer_GlobalQty extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {

        $manage_stock = Mage::getStoreConfig("cataloginventory/item_options/manage_stock");

        if (!in_array($row->getTypeId(), array('simple', 'virtual', 'downloadable'))) {
            return Mage::helper('advancedinventory')->__("-");
        }

        if ($row->getManageStock() || ($row->getUse_config_manage_stock() && $manage_stock )) {
            if ($row->getMultistock_enabled()) {
                $html = (int) $row->getQty();
            } else {
                $html = "<input class = 'keydown inventory_input' type = 'text' value = '" . (int) $row->getQty() . "' />";
            }
            $enabled = ($row->getMultistock_enabled()) ? 'enabled' : 'disabled';
            return "<span class = 'GlobalQty' id = 'GlobalQty_" . $row->getId() . "' multistock = '" . $enabled . "'>" . $html . "</span>";
        } else {
            return Mage::helper('advancedinventory')->__("X");
        }
    }

}
