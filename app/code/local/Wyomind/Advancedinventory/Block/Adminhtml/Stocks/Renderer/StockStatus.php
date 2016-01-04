<?php

class Wyomind_Advancedinventory_Block_Adminhtml_Stocks_Renderer_StockStatus extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {



        if (!in_array($row->getTypeId(), array('simple', 'virtual', 'downloadable'))) {
            return Mage::helper('advancedinventory')->__("-");
        }
        $checked = ($row->getStock_status()) ? 'checked' : '';

        $disabled = (Mage::getStoreConfig("advancedinventory/setting/auto_update_stock_status")) ? 'disabled' : '';

        $html = "<div style='text-align:center'><input $checked $disabled name='' class='StockStatus ' type='checkbox' value = '" . (int) $row->getIsInStock() . "' /></div>";



        return $html;
    }

}
