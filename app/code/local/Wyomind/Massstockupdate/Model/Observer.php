<?php

class Wyomind_Massstockupdate_Model_Observer {

    /**
     * Cronjob expression configuration
     */
    public function run($schedule) {

        $errors = array();
        $log = array();
        $log[] = "-------------------- CRON PROCESS --------------------";

        $collection = Mage::getModel('massstockupdate/import')->getCollection();
        $cnt = 0;

        foreach ($collection as $file) {

            try {

                $log[] = "--> Running profile : " . $file->getProfileName() . ' [#' . $file->getProfileId() . '] <--';


                $cron['curent']['localDate'] = Mage::getSingleton('core/date')->date('l Y-m-d H:i:s');
                $cron['curent']['gmtDate'] = Mage::getSingleton('core/date')->gmtDate('l Y-m-d H:i:s');
                $cron['curent']['localTime'] = Mage::getSingleton('core/date')->timestamp();
                $cron['curent']['gmtTime'] = Mage::getSingleton('core/date')->gmtTimestamp();


                $cron['file']['localDate'] = Mage::getSingleton('core/date')->date('l Y-m-d H:i:s', $file->getImportedAt());
                $cron['file']['gmtDate'] = $file->getImportedAt();
                $cron['file']['localTime'] = Mage::getSingleton('core/date')->timestamp($file->getImportedAt());
                $cron['file']['gmtTime'] = strtotime($file->getImportedAt());

                /* Magento getGmtOffset() is bugged and doesn't include daylight saving time, the following workaround is used */
                // date_default_timezone_set(Mage::app()->getStore()->getConfig('general/locale/timezone'));
                // $date = new DateTime();
                //$cron['offset'] = $date->getOffset() / 3600;
                $cron['offset'] = Mage::getSingleton('core/date')->getGmtOffset("hours");



                $log[] = '   * Last update : ' . $cron['file']['gmtDate'] . " GMT / " . $cron['file']['localDate'] . ' GMT' . $cron['offset'];
                $log[] = '   * Current date : ' . $cron['curent']['gmtDate'] . " GMT / " . $cron['curent']['localDate'] . ' GMT' . $cron['offset'];


                $cronExpr = json_decode($file->getCronSetting());
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
                             
                            if ($file->importProcess()) {
                                $done = true;
                                $cnt++;
                                $log[] = '   * EXECUTED!';
                            }
                        }

                        $i++;
                    }
                }
            } catch (Exception $e) {
                $log[] = '   * ERROR! ' . ($e->getMessage());
            }
            if(!$done)$log[] = '   * SKIPPED!';
        }
        


        if (Mage::getStoreConfig("massstockupdate/import/enable_report")) {
            foreach (explode(',', Mage::getStoreConfig("massstockupdate/import/emails")) as $email) {
                try {
                    if ($cnt)
                        mail($email, Mage::getStoreConfig("massstockupdate/import/report_title"), "\n".implode($log, "\n"));
                } catch (Exception $e) {
                    $log[] = '   * EMAIL ERROR! ' . ($e->getMessage());
                }
            }
        };
        if (isset($_GET['msi']))
            echo "<br/>".implode($log, "<br/>");
        Mage::log("\n".implode($log, "\n"), null, "MassStockUpate-cron.log");
       
    }

}