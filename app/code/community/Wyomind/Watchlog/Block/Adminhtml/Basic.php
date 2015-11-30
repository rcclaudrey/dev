<?php

class Wyomind_Watchlog_Block_Adminhtml_Basic extends Mage_Adminhtml_Block_Widget_Grid_Container {

    public function __construct() {

        $this->_controller = "adminhtml_basic";
        $this->_blockGroup = "watchlog";
        $this->_headerText = Mage::helper("watchlog")->__("Watchlog");
        parent::__construct();
        $this->setTemplate('watchlog/basic.phtml');
        
    }

}
