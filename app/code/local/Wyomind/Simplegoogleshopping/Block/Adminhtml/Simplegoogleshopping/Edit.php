<?php

class Wyomind_Simplegoogleshopping_Block_Adminhtml_Simplegoogleshopping_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {

    public function __construct() {
        parent::__construct();
        $this->_objectId = 'simplegoogleshopping_id';
        $this->_controller = 'adminhtml_simplegoogleshopping';
        $this->_blockGroup = 'simplegoogleshopping';

        if (Mage::registry('simplegoogleshopping_data')->getId()) {
            $this->_addButton('copy', array(
                'label' => Mage::helper('adminhtml')->__('Duplicate'),
                'onclick' => "$('simplegoogleshopping_id').remove(); editForm.submit();",
                'class' => 'add',
            ));

            $this->_addButton('generate', array(
                'label' => Mage::helper('adminhtml')->__('Generate'),
                'onclick' => "$('generate').value=1; editForm.submit();",
                'class' => 'add',
            ));
            $this->_addButton('continue', array(
                'label' => Mage::helper('adminhtml')->__('Save'),
                'onclick' => "$('continue').value=1; editForm.submit();",
                'class' => 'add',
            ));

            $this->_removeButton('save');
            $this->_removeButton('reset');
        }
    }

    public function getHeaderText() {
        if (Mage::registry('simplegoogleshopping_data') && Mage::registry('simplegoogleshopping_data')->getId()) {
            return $this->__('Modify data feed : ') . ' ' . $this->htmlEscape(Mage::registry('simplegoogleshopping_data')->getSimplegoogleshoppingFilename()) . '<br />';
        } else {
            return $this->__('Create a new data feed') . '<br />';
            ;
        }
    }

}
