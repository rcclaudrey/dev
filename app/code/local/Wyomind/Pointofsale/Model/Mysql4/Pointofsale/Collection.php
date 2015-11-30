<?php

class Wyomind_Pointofsale_Model_Mysql4_Pointofsale_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('pointofsale/pointofsale');
    }
}