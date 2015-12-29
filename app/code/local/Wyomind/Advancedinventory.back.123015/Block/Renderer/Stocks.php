<?php

class Wyomind_Advancedinventory_Block_Renderer_Stocks extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    protected function getStoreId() {

        $storeId = (int) $this->getRequest()->getParam('store', 0);

        return Mage::app()->getStore($storeId)->getStoreId();
    }

    public function render(Varien_Object $row) {
        $html = '';

        if (in_array($row->getTypeId(), array('simple', 'virtual', 'downloadable'))) {

            if ($this->getStoreId())
                $places = Mage::getModel('pointofsale/pointofsale')->getPlacesByStoreId($this->getStoreId());
            else
                $places = Mage::getModel('pointofsale/pointofsale')->getPlaces();
            if (Mage::getModel('advancedinventory/advancedinventory')->getLocalGlobalStockByProductId($row->getId())->getManageLocalStock()) {
                $html = (int) 0;
                foreach ($places as $p) {
                    $data = Mage::getModel('advancedinventory/advancedinventory')->getLocalStockQty($row->getId(), $p->getPlaceId());

                    $html += $data["quantity_in_stock"];
                }
            }
            else
                $html = "-";
        }
        else
            $html = "-";
        return (string) $html;
    }

}
