<?php

class Wyomind_Advancedinventory_Block_Adminhtml_Pointofsale_Edit_Tab_AdvancedInventory extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {


        $form = new Varien_Data_Form();

        $this->setForm($form);


        $model = Mage::getModel('pointofsale/pointofsale');

        $model->load($this->getRequest()->getParam('place_id'));
        if (Mage::getStoreConfig("advancedinventory/setting/autoassign_order")) {
            $fieldset = $form->addFieldset('ai_settings_1', array('legend' => Mage::helper('advancedinventory')->__('Inventory setting')));


            $fieldset->addField('use_assignation_rules', 'select', array(
                'label' => Mage::helper('pointofsale')->__('Assignation method'),
                'name' => 'use_assignation_rules',
                'class' => 'use_assignation_rules',
                "options" => array(
                    0 => "Do not assign any order",
                    1 => "Assign orders when product is available",
                    2 => "Assign orders depending on specific rules"
                ),
                'note' => 'Assign order to one/several warehouses',
            ));


            $fieldset->addField('inventory_assignation_rules', 'textarea', array(
                'label' => Mage::helper('pointofsale')->__('Assignation rules'),
                'name' => 'inventory_assignation_rules',
                'class' => 'inventory_assignation_rules',
                'note' => 'Assign to this inventory all orders which shipping address matches with this rule',
            ));


            $fieldset->addField('inventory_notification', 'text', array(
                'label' => Mage::helper('pointofsale')->__('Order notifications'),
                'name' => 'inventory_notification',
                'class' => 'inventory_notification',
                'note' => 'Email recipients separated with a coma (,)',
            ));
            $link = Mage::getUrl('advancedinventory/rss/stock', array('wh' => $this->getRequest()->getParam('place_id')));
            $fieldset->addField('rss_feed', 'link', array(
                'label' => Mage::helper('pointofsale')->__('Low stock notification feed'),
                'name' => 'rss_feed',
                'note' => '<a target="_blank" href="' . $link . '">' . $link . '</a>',
            ));
        }

        $fieldset = $form->addFieldset('ai_settings_2', array('legend' => Mage::helper('advancedinventory')->__('Default settings for products')));

        $fieldset->addField('default_stock_management', 'select', array(
            'label' => Mage::helper('pointofsale')->__('Stock management'),
            'name' => 'default_stock_management',
            "options" => array(
                1 => Mage::helper('advancedinventory')->__("Enabled"),
                0 => Mage::helper('advancedinventory')->__("Disabled"),
            ),
        ));
        $fieldset->addField('default_use_default_setting_for_backorder', 'select', array(
            'label' => Mage::helper('pointofsale')->__('Use config setting for backorders'),
            'name' => 'default_use_default_setting_for_backorder',
            "options" => array(
                1 => Mage::helper('advancedinventory')->__('yes'),
                0 => Mage::helper('advancedinventory')->__('no'),
            ),
            "selected" => 1
        ));
        $fieldset->addField('default_allow_backorder', 'select', array(
            'label' => Mage::helper('pointofsale')->__('Backorders status'),
            'name' => 'default_allow_backorder',
            "options" => array(
                0 => Mage::helper('advancedinventory')->__('No backorders'),
                1 => Mage::helper('advancedinventory')->__('Allow Qty below 0'),
                2 => Mage::helper('advancedinventory')->__('Allow Qty below 0 and Notify Customer'),
            ),
        ));
//        $fieldset->addField('group', 'text', array(
//            'label' => Mage::helper('pointofsale')->__('Store group code'),
//            'name' => 'group',
//            'class' => 'group',
//            'note' => 'Stores that share the same store code will be decreased synchronously',
//        ));
        $customField = $fieldset->addField('letsgo', 'text',null);

        $customField->setRenderer($this->getLayout()->createBlock('advancedinventory/adminhtml_pointofsale_edit_tab_renderer_button'));

        if (Mage::getSingleton('adminhtml/session')->getPointofsaleData()) {

            $form->setValues(Mage::getSingleton('adminhtml/session')->getPointofsaleData());

            Mage::getSingleton('adminhtml/session')->getPointofsaleData(null);
        } elseif (Mage::registry('pointofsale_data') && $this->getRequest()->getParam('place_id')) {

            $form->setValues($model);
        }

        if (version_compare(Mage::getVersion(), '1.3.0', '>')) {

            $this->setChild('form_after', $this->getLayout()->createBlock('adminhtml/widget_form_element_dependence')
                            ->addFieldMap('use_assignation_rules', 'use_assignation_rules')
                            ->addFieldMap('inventory_assignation_rules', 'inventory_assignation_rules')
                            ->addFieldDependence('inventory_assignation_rules', 'use_assignation_rules', 2)
                            ->addFieldMap('default_stock_management', 'default_stock_management')
                            ->addFieldMap('default_use_default_setting_for_backorder', 'default_use_default_setting_for_backorder')
                            ->addFieldMap('default_allow_backorder', 'default_allow_backorder')
                            ->addFieldDependence('default_use_default_setting_for_backorder', 'default_stock_management', 1)
                            ->addFieldDependence('default_allow_backorder', 'default_stock_management', 1)
                            ->addFieldDependence('default_allow_backorder', 'default_use_default_setting_for_backorder', 0));
        }



        return parent::_prepareForm();
    }

}
