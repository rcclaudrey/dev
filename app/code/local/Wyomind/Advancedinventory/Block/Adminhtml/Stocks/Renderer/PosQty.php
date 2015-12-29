<?php

class Wyomind_Advancedinventory_Block_Adminhtml_Stocks_Renderer_PosQty extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {
        $manage_stock = Mage::getStoreConfig("cataloginventory/item_options/manage_stock");

        if ($row->getManageStock() || ($row->getUse_config_manage_stock() && $manage_stock )) {
            if (in_array($row->getTypeId(), array('simple', 'virtual', 'downloadable'))) {
                $data = Mage::getModel('advancedinventory/stock')->getStockByProductIdAndPlaceId($row->getId(), $this->getColumn()->getPlaceId());
                $qty = (int) ($data->getQuantityInStock());
                $enabled = $data->getManageStock();

                if (!$row->getMultistock_enabled())
                    $html = "-";
                else {
                    if (!$enabled)
                        return Mage::helper('advancedinventory')->__("X");
                    else
                        $html = "<input class='keydown inventory_input' value='" . $qty . "' / >";
                    if (Mage::helper("advancedinventory/data")->isBackorderable($data))
                        $html .="<div title='Backorder allowed' class='ai-marker backorder'></div>";
                }
            } else
                $html = "-";

            return "<span class='PosQty' id='PosQty_" . $row->getId() . "_" . $this->getColumn()->getPlaceId() . "'>" . $html . "</span>";
        }
        else {
            return Mage::helper('advancedinventory')->__("X");
        }
    }

}
