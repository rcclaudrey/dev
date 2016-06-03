<?php

class Vikont_OEMGrid_Model_Mysql4_Part_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{

    protected function _construct()
    {
        parent::_construct();
        $this->_init('oemgrid/part');
    }

}
