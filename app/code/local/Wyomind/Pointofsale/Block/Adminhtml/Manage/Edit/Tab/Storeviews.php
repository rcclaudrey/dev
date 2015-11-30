<?php

class Wyomind_Pointofsale_Block_Adminhtml_Manage_Edit_Tab_Storeviews extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('storeviews', array('legend' => Mage::helper('pointofsale')->__('Store Views Selection')));

        $model = Mage::getModel('pointofsale/pointofsale');
        $model->load($this->getRequest()->getParam('place_id'));
        $_store_view = Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true);
        array_shift($_store_view);
        array_unshift($_store_view, array('value' => 0, 'label' => Mage::helper('pointofsale')->__('No Store View')));

        $fieldset->addField('store_id', 'multiselect', array(
            'name' => 'store_id[]',
            'label' => Mage::helper('cms')->__('Store View'),
            'title' => Mage::helper('cms')->__('Store View'),
            'class' => 'validate-select',
            'required' => true,
            'values' => $_store_view,
        ));


         if (Mage::getSingleton('adminhtml/session')->getPointofsalePlaceData()) {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getPointofsalePlaceData());
            Mage::getSingleton('adminhtml/session')->getPointofsalePlaceData(null);
        } elseif (Mage::registry('pointofsale_data') && $this->getRequest()->getParam('place_id')) {
            $form->setValues($model);
            $values=array();
            if ($model->getStoreId()!=null) {
                
                foreach (explode(',',$model->getStoreId()) as $k=>$v) {
                    $values[] = $v;
                }
                $form->getElement('store_id')->setValue($values);
            }
            else
               $form->getElement('store_id')->setValue(-1);
        }
        return parent::_prepareForm();
    }

}