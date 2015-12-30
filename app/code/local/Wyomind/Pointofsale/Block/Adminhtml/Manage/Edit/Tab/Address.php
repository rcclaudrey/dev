<?php

class Wyomind_Pointofsale_Block_Adminhtml_Manage_Edit_Tab_Address extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('address', array('legend' => Mage::helper('pointofsale')->__('Address & Contact')));
        $model = Mage::getModel('pointofsale/pointofsale');
        $model->load($this->getRequest()->getParam('place_id'));

        $fieldset->addField('address_line_1', 'text', array(
            'label' => Mage::helper('pointofsale')->__('Address line 1'),
            'name' => 'address_line_1',
            'class' => 'required-entry',
            'required' => true,
        ));

        $fieldset->addField('address_line_2', 'text', array(
            'label' => Mage::helper('pointofsale')->__('Address line 2'),
            'name' => 'address_line_2',
        ));

        $fieldset->addField('city', 'text', array(
            'label' => Mage::helper('pointofsale')->__('City'),
            'class' => 'required-entry',
            'name' => 'city',
            'required' => true,
        ));

        $fieldset->addField('postal_code', 'text', array(
            'label' => Mage::helper('pointofsale')->__('Postal code'),
            'class' => 'required-entry',
            'name' => 'postal_code',
            'required' => true,
        ));



        $country = $fieldset->addField('country_code', 'select', array(
            'name' => 'country_code',
            'label' => Mage::helper('pointofsale')->__('Country'),
            'values' => Mage::getModel('adminhtml/system_config_source_country')->toOptionArray(),
            'class' => 'required-entry',
            'required' => true,
            'onchange' => 'getstate(this)',
        ));



        /*
         * Add Ajax to the Country select box html output
         */
        $country->setAfterElementHtml("<script type=\"text/javascript\">
            function getstate(selectElement){
                var reloadurl = '" . $this
                        ->getUrl('pointofsale/adminhtml_manage/state') . "country/' + selectElement.value;
                new Ajax.Request(reloadurl, {
                    method: 'get',
                    onLoading: function (stateform) {
                        $('state').update('Searching…');
                    },
                    onComplete: function(stateform) {
                        $('state').update(stateform.responseText);
                    }
                });
            }
        </script>");
        $stateCollection = Mage::getModel('directory/region')->getResourceCollection()->addCountryFilter($model->getCountryCode())->load();
        if ($this->getRequest()->getParam('place_id')) {
            foreach ($stateCollection as $_state) {
                $states[] = array('value' => $_state->getCode(), 'label' => $_state->getDefaultName());
            }
        }
        else{
             $states[] = array('value' => null, 'label' => $this->__('--Please select a country--'));
        }
        $fieldset->addField('state', 'select', array(
            'label' => Mage::helper('pointofsale')->__('State'),
            'name' => 'state',
            'values' => $states,
        ));


        $fieldset->addField('main_phone', 'text', array(
            'label' => Mage::helper('pointofsale')->__('Main phone'),
            'name' => 'main_phone',
        ));
         $fieldset->addField('email', 'text', array(
            'label' => Mage::helper('pointofsale')->__('Email'),
            'name' => 'email',
        ));

        $fieldset->addField('image', 'image', array(
            'label' => Mage::helper('pointofsale')->__('Image'),
            'name' => 'image',
        ));
        $fieldset->addField('description', 'textarea', array(
            'label' => Mage::helper('pointofsale')->__('Description'),
            'name' => 'description',
        ));
        $fieldset->addField('hours', 'hidden', array(
            'name' => 'hours',
        ));
        $fieldset->addField('clone-hours', 'hours', array(
            'label' => Mage::helper('pointofsale')->__('Hours'),
            'name' => 'clone-hours',
        ));

        if (Mage::getSingleton('adminhtml/session')->getPointofsalePlaceData()) {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getPointofsalePlaceData());
            Mage::getSingleton('adminhtml/session')->getPointofsalePlaceData(null);
        } elseif (Mage::registry('pointofsale_data') && $this->getRequest()->getParam('place_id')) {
            $form->setValues($model);
            $collection = Mage::getModel('pointofsale/pointofsale')->getPlace($this->getRequest()->getParam('place_id'));
        }



        return parent::_prepareForm();
    }

}
