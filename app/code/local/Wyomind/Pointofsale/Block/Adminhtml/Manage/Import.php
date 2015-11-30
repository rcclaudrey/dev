<?php

class Wyomind_Pointofsale_Block_Adminhtml_Manage_Import extends Mage_Adminhtml_Block_Widget_Form_Container {

    public function __construct() {
        parent::__construct();

        $this->_objectId = 'import';
        $this->_blockGroup = 'pointofsale';
        $this->_controller = 'adminhtml_manage';

       $this->_updateButton('save', 'label', Mage::helper('pointofsale')->__('Import file'));
       $this->_removeButton('delete');


    }
    
    

    public function getHeaderText() {

        return Mage::helper('pointofsale')->__('Import a csv file');
    }

    

}