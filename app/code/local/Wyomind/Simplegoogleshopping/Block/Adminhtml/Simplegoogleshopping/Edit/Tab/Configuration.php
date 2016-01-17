<?php

class Wyomind_Simplegoogleshopping_Block_Adminhtml_Simplegoogleshopping_Edit_Tab_Configuration extends Mage_Adminhtml_Block_Widget_Form {

    protected function cmp($a, $b) {

        return ($a['frontend_label'] < $b['frontend_label']) ? -1 : 1;
    }

    protected function _prepareForm() {
        $form = new Varien_Data_Form();
        $model = Mage::getModel('simplegoogleshopping/simplegoogleshopping');

        $model->load($this->getRequest()->getParam('id'));

        $this->setForm($form);
        $fieldset = $form->addFieldset('simplegoogleshopping_form', array('legend' => $this->__('Configuration')));

        if ($model->getId()) {
            $fieldset->addField('simplegoogleshopping_id', 'hidden', array(
                'name' => 'simplegoogleshopping_id',
            ));
        }
        $fieldset->addField('cron_expr', 'hidden', array(
            'name' => 'cron_expr',
            'value' => $model->getCronExpr()
        ));
        $fieldset->addField('simplegoogleshopping_category_filter', 'hidden', array(
            'name' => 'simplegoogleshopping_category_filter',
            'value' => $model->getSimplegoogleshoppingCategoryFilter()
        ));
        $fieldset->addField('simplegoogleshopping_category_type', 'hidden', array(
            'name' => 'simplegoogleshopping_category_type',
            'value' => $model->getSimplegoogleshoppingCategoryType()
        ));
        $fieldset->addField('simplegoogleshopping_categories', 'hidden', array(
            'name' => 'simplegoogleshopping_categories',
            'value' => $model->getSimplegoogleshoppingCategories()
        ));



        $fieldset->addField('simplegoogleshopping_attributes', 'hidden', array(
            'name' => 'simplegoogleshopping_attributes',
            'value' => $model->getSimplegoogleshoppingAttributes()
        ));




        $fieldset->addField('simplegoogleshopping_filename', 'text', array(
            'label' => $this->__('Filename'),
            'name' => 'simplegoogleshopping_filename',
            'class' => 'refresh ',
            'required' => true,
            'style' => 'width:400px',
            'value' => $model->getSimplegoogleshoppingFilename(),
        ));

        $fieldset->addField('simplegoogleshopping_path', 'text', array(
            'label' => $this->__('Path'),
            'name' => 'simplegoogleshopping_path',
            "class" => "",
            'required' => true,
            'style' => 'width:400px',
            'value' => $model->getSimplegoogleshoppingPath()
        ));


        $fieldset->addField('store_id', 'select', array(
            'label' => $this->__('Store View'),
            'title' => $this->__('Store View'),
            'name' => 'store_id',
            "class" => "",
            'required' => true,
            'value' => $model->getStoreId(),
            'values' => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm()
        ));

        $fieldset->addField('simplegoogleshopping_url', 'text', array(
            'label' => $this->__('Website url'),
            'name' => 'simplegoogleshopping_url',
            'style' => 'width:400px',
            'class' => 'refresh ',
            'required' => true,
            'value' => $model->getSimplegoogleshoppingUrl()
        ));

        $fieldset->addField('simplegoogleshopping_title', 'text', array(
            'label' => $this->__('Title'),
            'name' => 'simplegoogleshopping_title',
            'style' => 'width:400px',
            'class' => 'refresh',
            'required' => true,
            'value' => $model->getSimplegoogleshoppingTitle()
        ));
        $resource = Mage::getSingleton('core/resource');
        $read = $resource->getConnection('core_read');
        $tableEet = $resource->getTableName('eav_entity_type');
        $select = $read->select()->from($tableEet)->where('entity_type_code=\'catalog_product\'');
        $data = $read->fetchAll($select);
        $typeId = $data[0]['entity_type_id'];


        /*  Liste des  attributs disponible dans la bdd */

        $attributes = Mage::getResourceModel('eav/entity_attribute_collection')
                ->setEntityTypeFilter($typeId)
                ->addSetInfo()
                ->getData();
        foreach ($attributes as $attribute) {
            $attributesList[] = array("attribute_code" => $attribute['attribute_code'], "frontend_label" => $attribute['frontend_label'], "frontend_input" => $attribute['frontend_input']);
        }
        $attributesList[] = array("attribute_code" => "entity_id", "frontend_label" => "Product Id");
        $attributesList[] = array("attribute_code" => "qty", "frontend_label" => "Quantity");
        $attributesList[] = array("attribute_code" => "is_in_stock", "frontend_label" => "Is in stock");
        usort($attributesList, array("Wyomind_Simplegoogleshopping_Block_Adminhtml_Simplegoogleshopping_Edit_Tab_Configuration", "cmp"));

        $fieldset->addField('simplegoogleshopping_description', 'textarea', array(
            'label' => $this->__('Description'),
            'name' => 'simplegoogleshopping_description',
            'class' => 'refresh ',
            'required' => true,
            'style' => 'width:400px;height:100px',
            'value' => $model->getSimplegoogleshoppingDescription(),
            'after_element_html' => '<h3 style=" margin: 20px 0 0 0;"> <a style="text-decoration:none;" href="http://www.wyomind.com/google-shopping-magento-wizard.html" target="_blank" id="sgs-wizard">&#10150;Need help?  Did you read our Tutorial?</a></h3>',
        ));


        $fieldset->addField('simplegoogleshopping_xmlitempattern', 'textarea', array(
            'label' => $this->__('Xml template'),
            'name' => 'simplegoogleshopping_xmlitempattern',
            'class' => 'refresh',
            'required' => true,
            'style' => 'width:400px;height:350px ;letter-spacing:1px; width:400px;',
            'value' => $model->getSimplegoogleshoppingXmlitempattern(),
        ));



        $fieldset->addField('generate', 'hidden', array(
            'name' => 'generate',
            'value' => ''
        ));
        $fieldset->addField('continue', 'hidden', array(
            'name' => 'continue',
            'value' => ''
        ));
        $fieldset->addField('copy', 'hidden', array(
            'name' => 'copy',
            'value' => ''
        ));

        Mage::dispatchEvent('adminhtml_simplegoogleshopping_edit_tab_configuration_prepare_fieldset', array('fieldset' => $fieldset, 'model' => $model));



        if (Mage::registry('simplegoogleshopping_data'))
            $form->setValues(Mage::registry('simplegoogleshopping_data')->getData());


        $fieldset->addField('sample_url', 'hidden', array(
            'id' => 'preview_path',
            'value' => $this->getUrl('*/*/sample', array('simplegoogleshopping_id' => $this->getRequest()->getParam('id'), 'real_time_preview' => 1))
        ));
        $fieldset->addField('library_url', 'hidden', array(
            'id' => 'library_path',
            'value' => $this->getUrl('*/*/library')
        ));
        $fieldset->addField('report_url', 'hidden', array(
            'id' => 'library_path',
            'value' => $this->getUrl('*/*/report', array('simplegoogleshopping_id' => $this->getRequest()->getParam('id'), 'real_time_preview' => 1))
        ));
        return parent::_prepareForm();
    }

}
