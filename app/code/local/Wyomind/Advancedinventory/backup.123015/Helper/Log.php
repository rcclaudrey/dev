<?php

class Wyomind_Advancedinventory_Helper_Log extends Mage_Core_Helper_Abstract {

    public function insertRow($context, $action, $reference, $details, $user = false) {
        if (Mage::getStoreConfig("advancedinventory/system/use_log")) {
            $row = Mage::getModel("advancedinventory/log");
            if ($user == FALSE) {
                if (Mage::app()->getStore()->isAdmin()) {
                    $user = "Admin : " . Mage::getSingleton('admin/session')->getUser()->getUsername();
                } else {
                    if (Mage::getSingleton('customer/session')) {
                        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
                            $customer = Mage::getSingleton('customer/session')->getCustomer();
                            $user = "Customer : " . $customer->getName();
                        } else {
                            $user = "Customer : Guest";
                        }
                    } else {
                        $user = "SYSTEM";
                    }
                }
            }


            $datetime = Mage::getSingleton('core/date')->gmtDate('Y-m-d H:i:s');
            $data = array(
                "user" => $user,
                "datetime" => $datetime,
                "context" => $context,
                "action" => $action,
                "reference" => $reference,
                "details" => $details,
            );
            try {
                $row->setData($data)->save();
            } catch (Exception $exception) {
                die('Advanced Inventory :: Unable to write in journal.');
            }
        }
    }

}
