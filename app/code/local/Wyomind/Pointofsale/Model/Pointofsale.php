<?php

class Wyomind_Pointofsale_Model_Pointofsale extends Mage_Core_Model_Abstract {

    public function _construct() {
        parent::_construct();
        $this->_init('pointofsale/pointofsale');
    }

    public function getPlaces() {
        $collection = $this->getCollection()->setOrder('position','ASC');
       

        return $collection;
    }

    public function getPlace($id) {


        $collection = $this->getCollection();
        $collection->getSelect()
                ->where("place_id=" . $id . "");


        return $collection;
    }

    function getPlacesByStoreId($storeId) {

        $login = Mage::getSingleton('customer/session')->isLoggedIn();
        if (!Mage::app()->getStore()->isAdmin()) {
            if (!$login)
                $groupId = 0;
            else
                $groupId = Mage::getSingleton('customer/session')->getCustomerGroupId();
            $whereGroupId = " AND FIND_IN_SET(" . $groupId . ",main_table.customer_group)";
        } else
            $whereGroupId = null;

        $collection = $this->getCollection();
        $collection->getSelect()
                ->where("FIND_IN_SET(" . $storeId . ",main_table.store_id) " . $whereGroupId)->order('position ASC');

        return $collection;
    }

    function getCountries($storeId) {

        $collection = $this->getCollection();
        $collection->getSelect()
                ->where("FIND_IN_SET(" . $storeId . ",main_table.store_id) ")
                ->group('main_table.country_code');

        return $collection;
    }

}
