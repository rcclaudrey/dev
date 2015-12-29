<?php

class Wyomind_Advancedinventory_Block_Adminhtml_Stocks_Renderer_StoreViewQty extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    protected function getStoreId() {

        $storeId = (int) $this->getRequest()->getParam('store', 0);

        return Mage::app()->getStore($storeId)->getStoreId();
    }

    public function render(Varien_Object $row) {
        $html = '';
        $manage_stock = Mage::getStoreConfig("cataloginventory/item_options/manage_stock");
        if ($row->getManageStock() || ($row->getUse_config_manage_stock() && $manage_stock )) {
            if (in_array($row->getTypeId(), array('simple', 'virtual', 'downloadable'))) {




                $permissions = Mage::helper('advancedinventory/permissions')->getUserPermissions();
                $all = $permissions->isAdmin();
                $pos = $permissions->getPos();

                if ($this->getStoreId()) {
                    $places = Mage::getModel('pointofsale/pointofsale')->getPlacesByStoreId($this->getStoreId());
                } else {
                    $places = Mage::getModel('pointofsale/pointofsale')->getPlaces();
                }


                $html = (int) 0;
                foreach ($places as $p) {
                    if ((in_array($p->getPlaceId(), $pos) || $all)) {
                        $data = Mage::getModel('advancedinventory/stock')->getStockByProductIdAndPlaceId($row->getId(), $p->getPlaceId());

                        $html += $data["quantity_in_stock"];
                    }
                }
            } else
                $html = "-";
            $enabled = ($row->getMultistock_enabled()) ? 'enabled' : 'disabled';
            return "<span class='GlobalQty' id='GlobalQty_" . $row->getId() . "' multistock='" . $enabled . "'>" . $html . "</span>";
        }
        else {
            return Mage::helper('advancedinventory')->__("X");
        }
    }

}
