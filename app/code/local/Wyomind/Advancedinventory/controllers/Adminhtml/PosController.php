<?php

include getcwd() . '/app/code/local/Wyomind/Pointofsale/controllers/Adminhtml/ManageController.php';

class Wyomind_Advancedinventory_Adminhtml_PosController extends Wyomind_Pointofsale_Adminhtml_ManageController {

    public function applyAction() {
        $placeId = Mage::app()->getRequest()->getParam("place_id");

        if (!$placeId) {
            $this->_redirect("pointofsale/adminhtml_manage/index");
           
        }
        $default_stock_management = $this->getRequest()->getPost('default_stock_management');
        $default_use_default_setting_for_backorder = $this->getRequest()->getPost('default_use_default_setting_for_backorder');
        $default_allow_backorder = $this->getRequest()->getPost('default_allow_backorder');
        $pos = Mage::getModel("pointofsale/pointofsale")->getCollection()->count();

        $items = Mage::getModel('advancedinventory/item')
                ->setAttributeToFilter('manage_local_stock', 1)
                ->getCollection();
        $i = 0;
        foreach ($items as $item) {



            $data = array(
                "manage_stock" => $default_stock_management,
                "use_config_setting_for_backorders" => $default_use_default_setting_for_backorder,
                "backorder_allowed" => $default_allow_backorder,
                "place_id" => $placeId,
                "localstock_id" => $item->getId(),
                "product_id" => $item->getProductId(),
            );
            if ($pos < 2)
                $data["quantity_in_stock"] = Mage::getModel('cataloginventory/stock_item')->loadByProduct($item->getProductId())->getQty();


            $stock = Mage::getModel('advancedinventory/stock')->getStockByProductIdAndPlaceId($item->getProductId(), $placeId);
            if ($stock->getId() === null) {
                $data["id"] = null;
            } else {

                $data["id"] = $stock->getId();
            }
            try {
                Mage::getModel('advancedinventory/stock')->setData($data)->save();
            } catch (Exception $e) {
                Mage::getSingleton("adminhtml/session")->addError(Mage::helper("advancedinventory")->__("Unable to update the products."));
                break;
            }
            $i++;
        }


        Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("advancedinventory")->__(($i) . " product(s) have been updated."));

        $this->getRequest()->setParam("back", 1);
        parent::saveAction();
    }

}
