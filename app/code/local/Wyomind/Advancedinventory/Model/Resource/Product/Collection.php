<?php

class Wyomind_Advancedinventory_Model_Resource_Product_Collection extends Mage_Catalog_Model_Resource_Product_Collection {

    protected function _getClearSelect() {

        $select = clone $this->getSelect();

        $select->reset(Zend_Db_Select::ORDER);

        $select->reset(Zend_Db_Select::LIMIT_COUNT);

        $select->reset(Zend_Db_Select::LIMIT_OFFSET);

        $select->reset(Zend_Db_Select::COLUMNS);

        if (Mage::getSingleton('adminhtml/url')->getRequest()->getModuleName() == 'advancedinventory') {

            $select->reset(Zend_Db_Select::GROUP);
            $select->reset(Zend_Db_Select::HAVING);
        }



        return $select;
    }

}

