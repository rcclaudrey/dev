<?php

class Wyomind_Watchlog_Model_Watchlog extends Mage_Core_Model_Abstract {

    protected function _construct() {
        $this->_init("watchlog/watchlog");
    }

    public function getSummary() {


        $collection = Mage::getModel("watchlog/watchlog")->getCollection();
        $collection->getSelect()
                ->columns('COUNT(watchlog_id) as attempts')
                ->columns('MAX(date) as date')
                ->columns('SUM(IF(`type`=0,1,0)) as failed')
                ->columns('SUM(IF(`type`=1,1,0)) as succeeded')
                ->order("SUM(IF(`type`=0,1,0)) DESC")
                ->group("ip");

        return $collection;
    }

    public function getSummaryDay() {


        $collection = Mage::getModel("watchlog/watchlog")->getCollection();

        $collection->getSelect()
                ->columns('COUNT(watchlog_id) as nb')
                ->where("date >= '" . Mage::getModel('core/date')->gmtDate('Y-m-d H:i:s') . "' - interval 23 hour")
                ->group("concat(hour(date))")
                ->order("date asc")
                ->group("type");


        return $collection;
    }

    public function getSummaryMonth() {

        $collection = Mage::getModel("watchlog/watchlog")->getCollection();

        $collection->getSelect()
                ->columns('COUNT(watchlog_id) as nb')
                ->columns("CONCAT(year(date),'-',month(date),'-',day(date)) as date")
                ->where("date > '" . Mage::getModel('core/date')->gmtDate('Y-m-d H:i:s') . "' - INTERVAL 30 DAY")
                ->order("date asc")
                ->group("concat(year(date),'-',month(date),'-',day(date))")
                ->group("type");

        return $collection;
    }

    public function getFailedPercentFromDate($date = null) {

        $collection = Mage::getModel("watchlog/watchlog")->getCollection();

        $collection->getSelect()
                ->columns('SUM(IF(`type`=0,1,0))/COUNT(watchlog_id) as percent')
                ->where("date >= '" . $date . "'");


        return $collection->getFirstItem();
    }

}
