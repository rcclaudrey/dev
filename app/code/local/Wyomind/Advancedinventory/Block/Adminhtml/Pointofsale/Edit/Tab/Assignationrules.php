<?php

class Wyomind_Advancedinventory_Block_Adminhtml_Pointofsale_Edit_Tab_Assignationrules extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {



        $form = new Varien_Data_Form();

        $this->setForm($form);

        $fieldset = $form->addFieldset('address', array('legend' => Mage::helper('advancedinventory')->__('Order/Inventory assignation Rules')));

        $model = Mage::getModel('pointofsale/pointofsale');

        $model->load($this->getRequest()->getParam('place_id'));



        $fieldset->addField('inventory_assignation_rules', 'textarea', array(
            'label' => Mage::helper('pointofsale')->__('Assignation Rules'),
            'name' => 'inventory_assignation_rules',
            'class' => 'inventory_assignation_rules',
            'note' => 'Assign all orders matching with these delivery address to `' . $model->getName() . '` inventory.',
        ));


        $fieldset->addField('inventory_notification', 'text', array(
            'label' => Mage::helper('pointofsale')->__('Email notification recipients'),
            'name' => 'inventory_notification',
            'class' => 'inventory_notification',
            'note' => 'separate each email with a coma (,)',
        ));


        if (Mage::getSingleton('adminhtml/session')->getPointofsaleData()) {

            $form->setValues(Mage::getSingleton('adminhtml/session')->getPointofsaleData());

            Mage::getSingleton('adminhtml/session')->getPointofsaleData(null);
        } elseif (Mage::registry('pointofsale_data') && $this->getRequest()->getParam('place_id')) {

            $form->setValues($model);

            $collection = Mage::getModel('pointofsale/pointofsale')->getPlace($this->getRequest()->getParam('place_id'));
        }



        return parent::_prepareForm();
    }

}

