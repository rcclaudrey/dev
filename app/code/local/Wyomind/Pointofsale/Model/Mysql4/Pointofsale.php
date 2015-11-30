<?php

class Wyomind_Pointofsale_Model_Mysql4_Pointofsale extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the pointofsale_id refers to the key field in your database table.
        $this->_init('pointofsale/pointofsale', 'place_id');
    }
}