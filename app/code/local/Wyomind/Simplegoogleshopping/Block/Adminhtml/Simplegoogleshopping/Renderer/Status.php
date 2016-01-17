<?php

class Wyomind_Simplegoogleshopping_Block_Adminhtml_Simplegoogleshopping_Renderer_Status extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    const _SUCCEED = "SUCCEED";
    const _PENDING = "PENDING";
    const _PROCESSING = "PROCESSING";
    const _HOLD = "HOLD";
    const _FAILED = "FAILED";

    public function render(Varien_Object $row) {


        $dir = Mage::getBaseDir() . DS . 'var' . DS . 'tmp' . DS;
        $file = $dir . "sgs_" . $row->getId() . ".flag";

        $flag = new Varien_Io_File();
        $flag->open(array('path' => $dir));

        if ($flag->fileExists($file, false)) {
            $flag->streamOpen($file, 'r');

            $line = $flag->streamReadCsv(";");
        
            $stats = $flag->streamStat();
            if ($line[0] == $this::_PROCESSING) {

                $updated_at = $stats["mtime"];
                $task_time = $line[3];
                if (Mage::getSingleton('core/date')->gmtTimestamp() > $updated_at + ($task_time * 10)) {
                    $line[0] = 'FAILED';
                } elseif (Mage::getSingleton('core/date')->gmtTimestamp() > $updated_at + ($task_time * 2)) {
                    $line[0] = 'HOLD';
                }
            } elseif ($line[0] == $this::_SUCCEED) {
                $cron = array();
                $cron['curent']['localTime'] = Mage::getSingleton('core/date')->timestamp();
                $cron['file']['localTime'] = Mage::getSingleton('core/date')->timestamp($stats["mtime"]);
                $cronExpr = json_decode($row->getCronExpr());
                $i = 0;
                foreach ($cronExpr->days as $d) {

                    foreach ($cronExpr->hours as $h) {


                        $time = explode(':', $h);
                        if (date('l', $cron['curent']['localTime']) == $d) {
                            $cron['tasks'][$i]['localTime'] = strtotime(Mage::getSingleton('core/date')->date('Y-m-d')) + ($time[0] * 60 * 60) + ($time[1] * 60);
                        } else {
                            $cron['tasks'][$i]['localTime'] = strtotime("last " . $d, $cron['curent']['localTime']) + ($time[0] * 60 * 60) + ($time[1] * 60);
                        }
                        if ($cron['tasks'][$i]['localTime'] >= $cron['file']['localTime'] && $cron['tasks'][$i]['localTime'] <= $cron['curent']['localTime']) {
                            $line[0] = $this::_PENDING;
                            continue 2;
                        }
                        $i++;
                    }
                }
            }

            switch ($line[0]) {
                case $this::_SUCCEED:
                    $severity = 'notice';
                    $status = Mage::helper("simplegoogleshopping")->__($line[0]);
                    break;
                case $this::_PENDING:
                    $severity = 'minor';
                    $status = Mage::helper("simplegoogleshopping")->__($line[0]);
                    break;
                case $this::_PROCESSING:
                    $percent = round($line[1] * 100 / $line[2]);
                    $severity = 'minor';
                    $status = Mage::helper("simplegoogleshopping")->__($line[0]) . " [" . $percent . "%]";
                    break;
                case $this::_HOLD:
                    $severity = 'major';
                    $status = Mage::helper("simplegoogleshopping")->__($line[0]);
                    break;
                case $this::_FAILED:
                    $severity = 'critical';
                    $status = Mage::helper("simplegoogleshopping")->__($line[0]);
                    break;
                default :
                    $severity = 'critical';
                    $status = Mage::helper("simplegoogleshopping")->__("ERROR");
                    break;
            }
        } else {

            $severity = 'minor';
            $status = Mage::helper("simplegoogleshopping")->__($this::_PENDING);
        }
        $script = "<script language='javascript' type='text/javascript'>var updater_url='" . $this->getUrl('/simplegoogleshopping/updater') . "'</script>";
        return $script . "<span class='grid-severity-$severity updater' cron='" . $row->getCronExpr() . "' id='feed_" . $row->getId() . "'><span>" . ($status) . "</span></span>";
    }

}
