<?php

class Wyomind_Advancedinventory_Model_Mysql4_Advancedinventory extends Mage_Core_Model_Mysql4_Abstract {

    public function _construct() {

        // Note that the advancedinventory_id refers to the key field in your database table.

        $this->_init('advancedinventory/advancedinventory', 'id');
    }

}