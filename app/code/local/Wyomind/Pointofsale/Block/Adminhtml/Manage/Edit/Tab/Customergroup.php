<?php

class Wyomind_Pointofsale_Block_Adminhtml_Manage_Edit_Tab_Customergroup extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('customergroup', array('legend' => Mage::helper('pointofsale')->__('Customer Group Selection')));

        $model = Mage::getModel('pointofsale/pointofsale');
        $model->load($this->getRequest()->getParam('place_id'));
        $_customer_group = array();
        $customer_group = new Mage_Customer_Model_Group();
        $allGroups = $customer_group->getCollection()->toOptionHash();
        
        foreach ($allGroups as $key => $allGroup) {
            $_customer_group[$key] = array('value' => $key, 'label' => $allGroup);
        }
        array_unshift($_customer_group, array('value' => "-1", 'label' => Mage::helper('pointofsale')->__('No Customer Group')));


        $fieldset->addField('customer_group', 'multiselect', array(
            'name' => 'customer_group[]',
            'label' => Mage::helper('cms')->__('Customer Group'),
            'title' => Mage::helper('cms')->__('Customer Group'),
            'class' => 'validate-select',
            'required' => true,
            'values' => $_customer_group,
        ));


        if (Mage::getSingleton('adminhtml/session')->getPointofsalePlaceData()) {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getPointofsalePlaceData());
            Mage::getSingleton('adminhtml/session')->getPointofsalePlaceData(null);
        } elseif (Mage::registry('pointofsale_data') && $this->getRequest()->getParam('place_id')) {
            $form->setValues($model);
            $values=array();
            if ($model->getCustomerGroup()!=null) {
                
                foreach (explode(',',$model->getCustomerGroup()) as $k=>$v) {
                    $values[] = $v;
                }
                $form->getElement('customer_group')->setValue($values);
            }
            else
               $form->getElement('customer_group')->setValue(-1);
        }
        return parent::_prepareForm();
    }

}