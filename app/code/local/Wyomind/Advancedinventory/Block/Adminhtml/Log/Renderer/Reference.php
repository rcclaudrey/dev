<?php

class Wyomind_Advancedinventory_Block_Adminhtml_Log_Renderer_Reference extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {

        foreach (explode(",", $row->getReference()) as $ref) {
            $ref = explode("#", $ref);

            switch ($ref[0]) {
                case "S":
                    $store = Mage::getModel('core/store')->load($ref[1]);
                    $group = Mage::getModel('core/store_group')->load($store->getGroupId());
                    $website = Mage::getModel('core/website')->load($store->getWebsiteId());
                    $title[] = $website->getName() . " > " . $group->getName() . " > " . $store->getName();
                    break;
                case "O":
                    $data = Mage::getModel("sales/order")->load($ref[1])->getIncrementId();
                    $title[] = "Order #" . $data;
                    break;
                case "P":
                    $data = Mage::getModel("catalog/product")->load($ref[1])->getSku();
                    $title[] = "Sku : " . $data;
                    break;
                case "W":
                    $data = Mage::getModel("pointofsale/pointofsale")->load($ref[1])->getName();
                    $title[] = "WH/POS : " . $data;
                    break;
            }
        };
        return "<span title=\"" . implode("\n", $title) . "\">" . ($row->getReference()) . "</span>";
    }

}
