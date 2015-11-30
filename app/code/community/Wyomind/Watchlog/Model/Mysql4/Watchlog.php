<?php
class Wyomind_Watchlog_Model_Mysql4_Watchlog extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init("watchlog/watchlog", "watchlog_id");
    }
}