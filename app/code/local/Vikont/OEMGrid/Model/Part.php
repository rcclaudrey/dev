<?php

class Vikont_OEMGrid_Model_Part extends Mage_Core_Model_Abstract
{

    public function _construct()
    {
        parent::_construct();
        $this->_init('oemgrid/part');
    }

}