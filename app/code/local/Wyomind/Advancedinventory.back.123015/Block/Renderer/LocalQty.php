<?php

class Wyomind_Advancedinventory_Block_Renderer_LocalQty extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {

        if (in_array($row->getTypeId(), array('simple', 'virtual', 'downloadable'))) {
            $data = Mage::getModel('advancedinventory/advancedinventory')->getLocalStockQty($row->getId(), $this->getColumn()->getPlaceId());
            ($data->getQuantityInStock()) ? $value = $data->getQuantityInStock() : $value = 0;
            if (in_array($this->getColumn()->getCurrentStore(), explode(',', $this->getColumn()->getStoreId())) || $this->getColumn()->getCurrentStore() == 0)
                $disabled = '';
            else
                $disabled = 'disabled';
            if (!$row->getManageLocalStock())
                return "<span class='foo_local_stock_qty_" . $row->getId() . "'>-</span>
                        <input class='keydown " . $disabled . " local_stock_qty_" . $row->getId() . "' disabled type='text' onchange='InventoryManager.updateStocks(" . $row->getId() . ",false)' style='display:none;text-align:center; width:50px;' name='inventory[" . $row->getId() . "][local_stock][" . $this->getColumn()->getPlaceId() . "][qty]' value='" . $value . "'/>
                        <input type='hidden' value='" . $data->getId() . "' id='inventory_" . $row->getId() . "_local_stock_" . $this->getColumn()->getPlaceId() . "_stock_id' name='inventory[" . $row->getId() . "][local_stock][" . $this->getColumn()->getPlaceId() . "][stock_id]'/>";
            else {
                return "<span class='foo_local_stock_qty_" . $row->getId() . "' style='display:none'>-</span>
                        <input class='keydown " . $disabled . " local_stock_qty_" . $row->getId() . "' onchange='InventoryManager.updateStocks(" . $row->getId() . ",false)' $disabled type='text' style='text-align:center; width:50px;' name='inventory[" . $row->getId() . "][local_stock][" . $this->getColumn()->getPlaceId() . "][qty]' value='" . $value . "' / >
                        <input type='hidden' value='" . $data->getId() . "' id='inventory_" . $row->getId() . "_local_stock_" . $this->getColumn()->getPlaceId() . "_stock_id' name='inventory[" . $row->getId() . "][local_stock][" . $this->getColumn()->getPlaceId() . "][stock_id]' / > ";
            }
        }
        else
            return "-";
    }

}
