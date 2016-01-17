<?php

class Wyomind_Simplegoogleshopping_Helper_Data extends Mage_Core_Helper_Data {

    public function checkHeartbeat() {

        $lastHeartbeat = $this->getLastHeartbeat();
        if ($lastHeartbeat === false) {
            // no cron task found
            Mage::getSingleton('core/session')->addError($this->__('No cron task found. <a href="https://www.wyomind.com/faq.html#How_do_I_fix_the_issues_with_scheduled_tasks" target="_blank">Check if cron is configured correctly.</a>'));
        } else {
            $timespan = $this->dateDiff($lastHeartbeat);
            if ($timespan <= 5 * 60) {
                Mage::getSingleton('core/session')->addSuccess($this->__('Scheduler is working. (Last cron task: %s minute(s) ago)', round($timespan / 60)));
            } elseif ($timespan > 5 * 60 && $timespan <= 60 * 60) {
                // cron task wasn't executed in the last 5 minutes. Heartbeat schedule could have been modified to not run every five minutes!
                Mage::getSingleton('core/session')->addNotice($this->__('Last cron task is older than %s minutes.', round($timespan / 60)));
            } else {
                // everything ok
                Mage::getSingleton('core/session')->addError($this->__('Last cron task is older than one hour. Please check your settings and your configuration!'));
            }
        }
    }

    public function getLastHeartbeat() {

        $schedules = Mage::getModel('cron/schedule')->getCollection();
        /* @var $schedules Mage_Cron_Model_Mysql4_Schedule_Collection */
        $schedules->getSelect()->limit(1)->order('executed_at DESC');
        $schedules->addFieldToFilter('status', Mage_Cron_Model_Schedule::STATUS_SUCCESS);

        $schedules->load();
        if (count($schedules) == 0) {
            return false;
        }
        $executedAt = $schedules->getFirstItem()->getExecutedAt();
        $value = Mage::getModel('core/date')->date(NULL, $executedAt);
        return $value;
    }

    public function dateDiff($time1, $time2 = NULL) {
        if (is_null($time2)) {
            $time2 = Mage::getModel('core/date')->date();
        }
        $time1 = strtotime($time1);
        $time2 = strtotime($time2);

        return $time2 - $time1;
    }

    public function getDuration($time) {
        if ($time < 60)
            $time = ceil($time) . ' sec. ';
        else
            $time = floor($time / 60) . ' min. ' . ($time % 60) . ' sec.';
        return $time;
    }

    public function generationStats($googleshopping) {





        $fileName = preg_replace('/^\//', '', $googleshopping->getSimplegoogleshoppingPath() . $googleshopping->getSimplegoogleshoppingFilename());
        $url = (Mage::app()->getStore($googleshopping->getStoreId())->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB) . $fileName);
        if (file_exists(BP . DS . $fileName)) {
            $report = unserialize($googleshopping->getSimplegoogleshoppingReport());
            $errors = count($report['required']) + count($report['toolong']) + count($report['toomany']) + count($report['invalid']);
            $warnings = count($report['recommended']);
            $time = $report['stats'][1];
            $items = $report['stats'][0];


            $stats = Mage::helper('simplegoogleshopping')->__('%s product%s exported in %s', $items, ($items > 1) ? "s" : null, Mage::helper('simplegoogleshopping')->getDuration($time));

            if ($report == null) {
                 return '<a href="' . $url . '?r=' . time() . '" target="_blank">' . $url . '</a><br>' 
                         . "[ " . Mage::helper('simplegoogleshopping')->__('The data feed must be generated prior to any report.') . " ]";
            } elseif (!($errors + $warnings)) {
                return '<a href="' . $url . '?r=' . time() . '" target="_blank">' . $url . '</a><br>'
                        . '[ ' . $stats . ", " . Mage::helper('simplegoogleshopping')->__('no error detected') . ' ]';
            } else {
                return '<a href="' . $url . '?r=' . time() . '" target="_blank">' . $url . '</a><br>'
                        . '[ ' . $stats . ", " . $errors . " " . Mage::helper('simplegoogleshopping')->__('error%s', ($errors > 1) ? "s" : null) . ', ' . $warnings . ' ' . Mage::helper('simplegoogleshopping')->__('warning%s', ($warnings > 1) ? "s" : null) . ' ]';
            }
        } else {
            return $url . "<br> [ " . Mage::helper('simplegoogleshopping')->__('no report available') . " ]";
        }
    }

    public function reportToHtml($data) {

        $notice = Mage::helper('simplegoogleshopping')->__("This report does not replace the error report from Google and is by no means a guarantee that your data feed will be approved by the Google team.");
        $html = null;

        foreach ($data["required"] as $error) {
            $html.="<h3>" . $error['message'] . " [" . $error['count'] . " " . Mage::helper('simplegoogleshopping')->__("items") . "]</h3>";
            if ($error['skus'] != "")
                $html.="<div class='exemples'>" . Mage::helper('simplegoogleshopping')->__("Examples:") . " <b>" . $error['skus'] . "</b></div>";
        };
        foreach ($data["recommended"] as $error) {
            $html.="<h3 style='color:orangered'>" . $error['message'] . " [" . $error['count'] . " " . Mage::helper('simplegoogleshopping')->__("items") . "]</h3>";
            if ($error['skus'] != "")
                $html.="<div class='exemples'>" . Mage::helper('simplegoogleshopping')->__("Examples:") . " <b>" . $error['skus'] . "</b></div>";
        };
        foreach ($data["toomany"] as $error) {
            $html.="<h3>" . $error['message'] . " [" . $error['count'] . " " . Mage::helper('simplegoogleshopping')->__("items") . "]</h3>";
            if ($error['skus'] != "")
                $html.="<div class='exemples'>" . Mage::helper('simplegoogleshopping')->__("Examples:") . " <b>" . $error['skus'] . "</b></div>";
        };
        foreach ($data["toolong"] as $error) {
            $html.="<h3>" . $error['message'] . " [" . $error['count'] . " " . Mage::helper('simplegoogleshopping')->__("items") . "]</h3>";
            if ($error['skus'] != "")
                $html.="<div class='exemples'>" . Mage::helper('simplegoogleshopping')->__("Examples:") . " <b>" . $error['skus'] . "</b></div>";
        };
        foreach ($data["invalid"] as $error) {
            $html.="<h3>" . $error['message'] . " [" . $error['count'] . " " . Mage::helper('simplegoogleshopping')->__("items") . "]</h3>";
            if ($error['skus'] != "")
                $html.="<div class='exemples'>" . Mage::helper('simplegoogleshopping')->__("Examples:") . " <b>" . $error['skus'] . "</b></div>";
        };

        if ($data == null) {
            return "<div id='dfm-report'>" . $html . "<br><br><b>" . Mage::helper('simplegoogleshopping')->__('The data feed must be generated prior to any report.') . "</b></div>";
        } elseif ($html !== null)
            return "<div id='dfm-report'>" . $notice . $html . "</div>";
        else
            return "<div id='dfm-report'>" . $notice . "<br><br><b>" . Mage::helper('simplegoogleshopping')->__('no error detected.') . "</b></div>";
    }

}
