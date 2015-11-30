<?php

class Wyomind_Watchlog_Block_Adminhtml_Advanced extends Mage_Adminhtml_Block_Widget_Grid_Container {

    
    public function __construct() {
        $this->_controller = 'adminhtml_advanced';

        $this->_blockGroup = 'watchlog';

        $this->_headerText = Mage::helper('watchlog')->__('Watchlog Summary');

        
        parent::__construct();
        $this->setTemplate('watchlog/advanced.phtml');
        $this->removeButton('add');
    }
    
    
}
