<?php

class Wyomind_Massstockupdate_Block_Adminhtml_Import extends Mage_Adminhtml_Block_Widget_Grid_Container {

    public function __construct() {

        $this->_controller = 'adminhtml_import';
        $this->_blockGroup = 'massstockupdate';
        $this->_headerText = Mage::helper('massstockupdate')->__('Mass Stock Update');
        $this->_addButtonLabel = Mage::helper('massstockupdate')->__('Create a new profile');
        parent::__construct();

        $this->_addButton('backup', array(
            'label' => Mage::helper('massstockupdate')->__('Create a stock backup'),
            'onclick' => 'document.location=\''.$this->getUrl('*/adminhtml_import/backup').'\'',
            'class' => 'go'
        ));
    }

    public function isSingleStoreMode() {
        if (!Mage::app()->isSingleStoreMode()) {
            return false;
        }
        return true;
    }

}