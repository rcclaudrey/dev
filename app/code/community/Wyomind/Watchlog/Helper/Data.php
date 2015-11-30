<?php

class Wyomind_Watchlog_Helper_Data extends Mage_Core_Helper_Abstract {

    public function checkNotification() {
        $last_notification = Mage::getStoreConfig("watchlogpro/settings/last_notification");
        $failed_limit = Mage::getStoreConfig("watchlogpro/settings/failed_limit");


        $percent = Mage::getModel("watchlog/watchlog")->getFailedPercentFromDate($last_notification)->getPercent();

        if ($percent > $failed_limit) {
            // add notif in inbox
            $notification_title = Mage::getStoreConfig("watchlogpro/settings/notification_title");
            $notification_description = Mage::getStoreConfig("watchlogpro/settings/notification_description");
            $notification_link = Mage::helper("adminhtml")->getUrl("/adminhtml/index/");
            $date = Mage::getSingleton('core/date')->gmtDate('Y-m-d H:i:s');

            $notify = Mage::getModel('adminnotification/inbox');
            $item = $notify->getCollection()->addFieldToFilter('title', array("eq" => "Watchlog security warning"))->addFieldToFilter('is_remove', array("eq" => 0));
            $data = $item->getLastItem()->getData();

            if ($data["notification_id"]) {
                $notify->load($data["notification_id"]);
                $notify->setUrl($notification_link);
                $notify->setDescription($this->__($notification_description, round($percent * 100), $notification_link));
                $notify->setData('is_read', 0)->save();
            } else {

                $notify->setUrl($notification_link);
                $notify->setDescription($this->__($notification_description, round($percent * 100), $notification_link));
                $notify->setTitle($this->__($notification_title));
                $notify->setSeverity(1);
                $notify->save();
            }
            // upate watchlogpro/settings/last_notification config
            Mage::getConfig()->saveConfig("watchlogpro/settings/last_notification", $date, "default", "0");
        }
    }

    public function checkWarning() {


        $last_notification = Mage::getStoreConfig("watchlogpro/settings/last_notification");
        $failed_limit = Mage::getStoreConfig("watchlogpro/settings/failed_limit");
        $percent = Mage::getModel("watchlog/watchlog")->getFailedPercentFromDate()->getPercent();
        $notification_details = Mage::getStoreConfig("watchlogpro/settings/notification_details");
        if ($percent > $failed_limit)
            Mage::getSingleton("core/session")->addError($this->__($notification_details, round($percent * 100)));
    }

}
