<?php

class Wyomind_Advancedinventory_Block_Adminhtml_Log extends Mage_Adminhtml_Block_Widget_Grid_Container {

    public function __construct() {

        $this->_controller = 'adminhtml_log';

        $this->_blockGroup = 'advancedinventory';

        $this->_headerText = Mage::helper('advancedinventory')->__('Stock movement journal');


        parent::__construct();
        $this->removeButton('reset');
        $this->removeButton('add');
    }

    public function isSingleStoreMode() {
        if (!Mage::app()->isSingleStoreMode()) {
            return false;
        }
        return true;
    }

}
