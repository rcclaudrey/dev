<?php

class Wyomind_Pointofsale_Block_Adminhtml_Manage_Import_Tab_Uploader extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('uploader', array('legend' => Mage::helper('pointofsale')->__('Choose the file to import')));


        $fieldset->addField('file', 'file', array(
            'label' => Mage::helper('pointofsale')->__('Tab delimited file only'),
            'name' => 'file',
        ));


        return parent::_prepareForm();
    }

}
