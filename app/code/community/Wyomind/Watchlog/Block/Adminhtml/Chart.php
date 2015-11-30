<?php

class Wyomind_Watchlog_Block_Adminhtml_Chart extends Mage_Adminhtml_Block_Widget_Container {

    const FAIL = 0;
    const SUCCESS = 1;
    const BLOCKED = 2;

    public function __construct() {

        $this->_controller = "adminhtml_chart";
        $this->_blockGroup = "watchlog";
        parent::__construct();
        $this->setTemplate('watchlog/chart.phtml');
    }

    public function getChartDataSummaryMonth() {

        $pro = Mage::helper('core')->isModuleEnabled("Wyomind_Watchlogpro") && Mage::helper('core')->isModuleOutputEnabled("Wyomind_Watchlogpro") && Mage::getConfig()->getNode('modules/Wyomind_Watchlogpro/active');

        $data = array();
        $headers = array(Mage::helper('watchlog')->__('Date'), Mage::helper('watchlog')->__('Success'), Mage::helper('watchlog')->__('Failed'));
        if ($pro)
            $headers[] = Mage::helper('watchlog')->__('Blocked');

        $data[] = $headers;

        $tmp_data = array();


        $current_timestamp = Mage::getModel('core/date')->gmtTimestamp();
        $yestermonth_timestamp = $current_timestamp - 29 * 24 * 60 * 60;
        while ($yestermonth_timestamp <= $current_timestamp) {
            $key = Mage::getModel('core/date')->date('Y-m-d', $yestermonth_timestamp);
            $tmp_data[$key] = array(self::FAIL => 0, self::SUCCESS => 0, self::BLOCKED => 0);
            $yestermonth_timestamp += 24 * 60 * 60;
        }

        $collection = Mage::getModel('watchlog/watchlog')->getSummaryMonth();
        foreach ($collection as $entry) {
            $key = Mage::getModel('core/date')->date('Y-m-d', strtotime($entry->getDate()));
            if (!isset($tmp_data[$key])) {
                $tmp_data[$key] = array(self::FAIL => 0, self::SUCCESS => 0, self::BLOCKED => 0);
            }
            $tmp_data[$key][$entry->getType()] = $entry->getNb();
        }
        ksort($tmp_data);
        foreach ($tmp_data as $date => $entry) {
            if ($pro)
                $data[] = array("#new Date('" . $date . "')#", (int) $entry[self::SUCCESS], (int) $entry[self::FAIL], (int) $entry[self::BLOCKED]);
            else
                $data[] = array("#new Date('" . $date . "')#", (int) $entry[self::SUCCESS], (int) $entry[self::FAIL]);
        }

        return $data;
    }

    public function getChartDataSummaryDay() {

        $pro = Mage::helper('core')->isModuleEnabled("Wyomind_Watchlogpro") && Mage::helper('core')->isModuleOutputEnabled("Wyomind_Watchlogpro") && Mage::getConfig()->getNode('modules/Wyomind_Watchlogpro/active');

        $data = array();
        $headers = array(Mage::helper('watchlog')->__('Date'), Mage::helper('watchlog')->__('Success'), Mage::helper('watchlog')->__('Failed'));
        if ($pro)
            $headers[] = Mage::helper('watchlog')->__('Blocked');

        $data[] = $headers;

        $tmp_data = array();
        
        $current_timestamp = Mage::getModel('core/date')->gmtTimestamp();
        $yesterday_timestamp = $current_timestamp - 23 * 60 * 60;
        while ($yesterday_timestamp <= $current_timestamp) {
            $key = Mage::getModel('core/date')->date('M d, Y H:00:00', $yesterday_timestamp);
            $tmp_data[$key] = array(self::FAIL => 0, self::SUCCESS => 0, self::BLOCKED => 0);
            $yesterday_timestamp += 60 * 60;
        }

        $collection = Mage::getModel('watchlog/watchlog')->getSummaryDay();
        foreach ($collection as $entry) {
            $key = Mage::getModel('core/date')->date('M d, Y H:00:00', strtotime($entry->getDate()));
            if (!isset($tmp_data[$key])) {
                $tmp_data[$key] = array(self::FAIL => 0, self::SUCCESS => 0, self::BLOCKED => 0);
            }
            $tmp_data[$key][$entry->getType()] = $entry->getNb();
        }

//        ksort($tmp_data);

        foreach ($tmp_data as $date => $entry) {
            if ($pro)
                $data[] = array("#new Date('" . $date . "')#", (int) $entry[self::SUCCESS], (int) $entry[self::FAIL], (int) $entry[self::BLOCKED]);
            else
                $data[] = array("#new Date('" . $date . "')#", (int) $entry[self::SUCCESS], (int) $entry[self::FAIL]);
        }
        return $data;
    }

}