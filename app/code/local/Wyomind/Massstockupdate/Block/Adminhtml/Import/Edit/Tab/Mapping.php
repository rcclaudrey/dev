<?php

class Wyomind_Massstockupdate_Block_Adminhtml_Import_Edit_Tab_Mapping extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {
       
        $form = new Varien_Data_Form();
        $model = Mage::getModel('massstockupdate/import');

        $model->load($this->getRequest()->getParam('profile_id'));

        $this->setForm($form);
        $fieldset = $form->addFieldset('massstockupdate_form', array('legend' => $this->__('File mapping')));


        $this->setTemplate('massstockupdate/mapping.phtml');


        if (Mage::registry('ordersexporttool_data'))
            $form->setValues(Mage::registry('ordersexporttool_data')->getData());

        return parent::_prepareForm();
    }

}