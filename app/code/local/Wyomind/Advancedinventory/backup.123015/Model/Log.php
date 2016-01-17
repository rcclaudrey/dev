<?php

class Wyomind_Advancedinventory_Model_Log extends Mage_Core_Model_Abstract {

    public function _construct() {
        parent::_construct();
        $this->_init('advancedinventory/log');
    }

    public function getColumn($column) {
        $collection = Mage::getModel("advancedinventory/log")->getCollection();
        $collection->getSelect()->distinct(true);
        $collection->getSelect()->group($column);
        return $collection;
    }

    public function getUsers() {
        $array = array();
        foreach ($this->getColumn('user') as $line) {
            $array[$line->getUser()] = $line->getUser();
        }
        return $array;
    }

    public function getActions() {
        $array = array();
        foreach ($this->getColumn('action') as $line) {
            $array[$line->getAction()] = $line->getAction();
        }
        return $array;
    }

    public function getContexts() {
        $array = array();
        foreach ($this->getColumn('context') as $line) {
            $array[$line->getContext()] = $line->getContext();
        }
        return $array;
    }

    function clean() {
        $history = Mage::getStoreConfig("advancedinventory/system/log_history");

        $todayTimestamp = Mage::getSingleton('core/date')->gmtTimestamp();
        $historyTimestamp = $todayTimestamp - $history * 86400;
        $historyDatetime = Mage::getSingleton('core/date')->gmtDate('Y-m-d H:i:s', $historyTimestamp);
        $collection = Mage::getModel("advancedinventory/log")->getCollection()->addFieldToFilter("datetime", array("lt" => $historyDatetime));
        try {
            foreach ($collection as $log) {
                $log->delete();
            }
        } catch (Exception $e) {
            Mage::log("Advanced Inventory says :" . $e->getMessage());
        }
    }

}
