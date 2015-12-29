<?php

class Wyomind_Advancedinventory_Block_Renderer_TotalLocalQty extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {
        if (in_array($row->getTypeId(), array('simple', 'virtual', 'downloadable'))) {
            if ($row->getManageLocalStock())
                return '<span id="foo_total_local_stock_qty_' . $row->getId() . '">' . $row->getTotalLocalQty() . "</span><input id='total_local_stock_qty_" . $row->getId() . "' type='hidden' value='" . $row->getStockId() . "' name='inventory[" . $row->getId() . "][total_local_stock_qty]'/>";
            else
                return '<span id="foo_total_local_stock_qty_' . $row->getId() . '">-</span>' . "<input type='hidden' id='total_local_stock_qty_" . $row->getId() . "' value='" . $row->getStockId() . "' name='inventory[" . $row->getId() . "][total_local_stock_qty]'/>";
        }
        else
            return "-";
    }

}
