<?php



class Wyomind_Massstockupdate_Model_Mysql4_Import extends Mage_Core_Model_Mysql4_Abstract

{

    public function _construct()

    {    

        // Note that the massstockupdate_id refers to the key field in your database table.

        $this->_init('massstockupdate/import', 'profile_id');

    }

}