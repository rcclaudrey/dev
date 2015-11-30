<?php

class Wyomind_Advancedinventory_Model_Resource_Eav_Mysql4_Product_Collection extends Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection {

    public function getSelectCountSql() {

        $this->_renderFilters();



        $countSelect = clone $this->getSelect();

        $countSelect->reset(Zend_Db_Select::ORDER);

        $countSelect->reset(Zend_Db_Select::LIMIT_COUNT);

        $countSelect->reset(Zend_Db_Select::LIMIT_OFFSET);

        $countSelect->reset(Zend_Db_Select::COLUMNS);

        if (Mage::getSingleton('adminhtml/url')->getRequest()->getModuleName() == 'advancedinventory') {

            $countSelect->reset(Zend_Db_Select::GROUP);
            $countSelect->reset(Zend_Db_Select::HAVING);
        }



        $countSelect->columns('COUNT(DISTINCT e.entity_id)');

        $countSelect->resetJoinLeft();



        return $countSelect;
    }

}

