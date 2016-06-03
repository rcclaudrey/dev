<?php

class Vikont_OEMGrid_Model_Mysql4_Part extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('oemgrid/part', 'id');
//		$this->_isPkAutoIncrement = true;
    }


}
