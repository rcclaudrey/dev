<?php

class Wyomind_Watchlog_Model_Observer {

    public function loginSuccess($observer) {

        $url = Mage::app()->getRequest()->getRequestUri();
        $login = $observer->getEvent()->getUser()->getUsername();

        $ip = Mage::helper('core/http')->getRemoteAddr();

        $data = array(
            "login" => $login,
            "ip" => $ip,
            "date" => Mage::getModel('core/date')->gmtDate('Y-m-d H:i:s'),
            "type" => 1,
            "useragent" => Mage::helper('core/http')->getHttpUserAgent(),
            "message" => "",
            "url" => $url
        );
        
         Mage::helper('watchlog/data')->checkNotification();
        

        $model = Mage::getModel('watchlog/watchlog')->load(0);
        $model->setData($data);
        $model->save();
    }

    public function loginFailed($observer) {
        $url = Mage::app()->getRequest()->getRequestUri();
        $login = $observer->getEvent()->getUserName();
        $message = $observer->getEvent()->getException()->getMessage();

        $ip = Mage::helper('core/http')->getRemoteAddr();

        $data = array(
            "login" => $login,
            "ip" => $ip,
            "date" => Mage::getModel('core/date')->gmtDate('Y-m-d H:i:s'),
            "type" => 0,
            "useragent" => Mage::helper('core/http')->getHttpUserAgent(),
            "message" => $message,
            "url" => $url
        );

        $model = Mage::getModel('watchlog/watchlog')->load(0);
        $model->setData($data);
        $model->save();
    }

    /**
     * Purge the data in the table every 30 minutes
     */
    public function purgeData() {
        $timestamp = Mage::getSingleton('core/date')->gmtTimestamp();
        $histolength = Mage::getStoreConfig("watchlogpro/settings/history");
        $delete_before = $timestamp - $histolength * 60 * 60 * 24;

        if ($histolength != 0) {

            $log = array();
            $log[] = "-------------------- PURGE PROCESS --------------------";
            $log[] = "current date : " . Mage::getSingleton('core/date')->gmtDate('Y-m-d H:i:s', $timestamp);
            $log[] = "deleting row before : " . Mage::getSingleton('core/date')->gmtDate('Y-m-d H:i:s', $delete_before);

            $resource = Mage::getSingleton('core/resource');
            $watchlog = $resource->getTableName('watchlog');
            $writeConnection = $resource->getConnection('core_write');
            $query = "DELETE FROM " . $watchlog . " WHERE date < '" . Mage::getSingleton('core/date')->gmtDate('Y-m-d H:i:s', $delete_before) . "'";
            $log[] = $query;
            $writeConnection->query($query);

            Mage::log("\n" . implode($log, "\n"), null, "Watchlog-cron.log");
        }
    }

    public function sendReport() {
        try {


            $log = array();

           $update = Mage::getModel('core/config_data')->getCollection()->addFieldToFilter("path",array("eq" => "watchlogpro/settings/last_report"))->getFirstItem()->getValue();
        
            $cronExpr = json_decode(Mage::getStoreConfig("watchlogpro/settings/cron"));
            $cron['curent']['localDate'] = Mage::getSingleton('core/date')->date('l Y-m-d H:i:s');
            $cron['curent']['gmtDate'] = Mage::getSingleton('core/date')->gmtDate('l Y-m-d H:i:s');
            $cron['curent']['localTime'] = Mage::getSingleton('core/date')->timestamp();
            $cron['curent']['gmtTime'] = Mage::getSingleton('core/date')->gmtTimestamp();


            $cron['file']['localDate'] = Mage::getSingleton('core/date')->date('l Y-m-d H:i:s', $update);
            $cron['file']['gmtDate'] = $update;
            $cron['file']['localTime'] = Mage::getSingleton('core/date')->timestamp($update);
            $cron['file']['gmtTime'] = strtotime($update);

            /* Magento getGmtOffset() is bugged and doesn't include daylight saving time, the following workaround is used */
            // date_default_timezone_set(Mage::app()->getStore()->getConfig('general/locale/timezone'));
            // $date = new DateTime();
            //$cron['offset'] = $date->getOffset() / 3600;
            $cron['offset'] = Mage::getSingleton('core/date')->getGmtOffset("hours");

            $log[] = "-------------------- REPORT PROCESS --------------------";

            $log[] = '   * Last update : ' . $cron['file']['gmtDate'] . " GMT / " . $cron['file']['localDate'] . ' GMT+' . $cron['offset'];
            $log[] = '   * Current date : ' . $cron['curent']['gmtDate'] . " GMT / " . $cron['curent']['localDate'] . ' GMT+' . $cron['offset'];

            $i = 0;
            $done = false;

            foreach ($cronExpr->days as $d) {

                foreach ($cronExpr->hours as $h) {
                    $time = explode(':', $h);
                    if (date('l', $cron['curent']['gmtTime']) == $d) {
                        $cron['tasks'][$i]['localTime'] = strtotime(Mage::getSingleton('core/date')->date('Y-m-d')) + ($time[0] * 60 * 60) + ($time[1] * 60);
                        $cron['tasks'][$i]['localDate'] = date('l Y-m-d H:i:s', $cron['tasks'][$i]['localTime']);
                    } else {
                        $cron['tasks'][$i]['localTime'] = strtotime("last " . $d, $cron['curent']['localTime']) + ($time[0] * 60 * 60) + ($time[1] * 60);
                        $cron['tasks'][$i]['localDate'] = date('l Y-m-d H:i:s', $cron['tasks'][$i]['localTime']);
                    }



                    if ($cron['tasks'][$i]['localTime'] >= $cron['file']['localTime'] && $cron['tasks'][$i]['localTime'] <= $cron['curent']['localTime'] && $done != true) {

                        $log[] = '   * Scheduled : ' . ($cron['tasks'][$i]['localDate'] . " GMT" . $cron['offset']);

                        /*
                         * app/locale/en_US/template/email/watchlog_report.html
                         */
                        $emailTemplate = Mage::getModel('core/email_template')->loadDefault('watchlog_report');
                        $date = Mage::getSingleton('core/date')->gmtDate("Y-m-d H:i:s", Mage::getSingleton('core/date')->gmtTimestamp() - Mage::getStoreConfig("watchlogpro/settings/report_period") * 86400);

                        $history = Mage::getModel("watchlog/watchlog")
                                        ->getCollection()->addFieldToFilter('date', array('gteq' => $date));


                        $history->getSelect()
                                ->columns('COUNT(watchlog_id) as attempts')
                                ->columns('MAX(date) as date')
                                ->columns('SUM(IF(type=0,1,0)) as failed')
                                ->columns('SUM(IF(type=1,1,0)) as succeeded')
                                ->columns('SUM(IF(type=2,1,0)) as blocked')
                                ->order("SUM(IF(type=0,1,0)) DESC")
                                ->group("ip");



                        foreach ($history as $line) {

                            $emailTemplateVariables['log'][] = array(
                                "ip" => $line->getIp(),
                                "attempts" => $line->getAttempts(),
                                "date" => $line->getDate(),
                                "failed" => $line->getFailed(),
                                "succeeded" => $line->getSucceeded(),
                                "blocked" => $line->getBlocked(),
                            );
                        }



                        $emailTemplateVariables['days'] = Mage::getStoreConfig("watchlogpro/settings/report_period");

                        $processedTemplate = $emailTemplate->getProcessedTemplate($emailTemplateVariables);

                        foreach (explode(',', Mage::getStoreConfig("watchlogpro/settings/report_emails")) as $email) {
                            $mail = Mage::getModel('core/email')
                                    ->setToEmail($email)
                                    ->setBody($processedTemplate)
                                    ->setSubject(Mage::getStoreConfig("watchlogpro/settings/report_title"))
                                    ->setFromEmail($email)
                                    ->setFromName('Magento | Watchlog')
                                    ->setType('html');
                            $mail->send();
                        }

                        Mage::getConfig()->saveConfig("watchlogpro/settings/last_report", Mage::getSingleton("core/date")->gmtDate("Y-m-d H:i:s"), "default", "0");
                        $done = true;
                    }
                    $i++;
                }
            }
        } catch (Exception $e) {
            $log[] = '   * ERROR! ' . ($e->getMessage());
        }
        if (!$done)
            $log[] = '   * SKIPPED!';


        if (isset($_GET['wl']))
            echo "<br/>" . implode($log, "<br/>");

        Mage::log("\n" . implode($log, "\n"), null, "Watchlog-cron.log");
    }

}
