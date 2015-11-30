<?php

class Wyomind_Massstockupdate_Block_Adminhtml_Import_Edit_Tab_Setting extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {
        $form = new Varien_Data_Form();
        $model = Mage::getModel('massstockupdate/import');
        $model->load($this->getRequest()->getParam('profile_id'));
        $this->setForm($form);

        $fieldset = $form->addFieldset('massstockupdate_form', array('legend' => $this->__('Profile Setting')));

        (isset($_GET['debug'])) ? $type = 'text' : $type = 'hidden';

        if ($this->getRequest()->getParam('profile_id')) {
            $fieldset->addField('profile_id', $type, array(
                'name' => 'profile_id',
                'value' => $model->getProfileId()
            ));
        }

        $fieldset->addField('profile_name', 'text', array(
            'name' => 'profile_name',
            'value' => $model->getProfileName(),
            'label' => Mage::helper('massstockupdate')->__('Profile name'),
            "required" => true,
        ));

        
        $fieldset->addField('file_system_type', 'select', array(
            'name' => 'file_system_type',
            'value' => $model->getFileSystemType(),
            'label' => Mage::helper('massstockupdate')->__('File location'),
            "required" => true,
            'values' => array(
                array(
                    'value' => 0,
                    'label' => 'Magento File System'
                ),
                array(
                    'value' => 1,
                    'label' => 'Ftp server'
                ),
            )
        ));
        
        $fieldset->addField('use_sftp', 'select', array(
            'label' => Mage::helper('massstockupdate')->__('Use SFTP'),
            'name' => 'use_sftp',
            'id' => 'use_sftp',
            'value' => $model->getUseSftp(),
            'required' => true,
            'values' => array(
                array(
                    'value' => 0,
                    'label' => $this->__('no')
                ),
                array(
                    'value' => 1,
                    'label' => $this->__('yes')
                )
            ),
        ));
        $fieldset->addField('ftp_active', 'select', array(
            'label' => Mage::helper('massstockupdate')->__('Use active mode'),
            'name' => 'ftp_active',
            'id' => 'ftp_active',
            'value' => $model->getFtpActive(),
            'required' => true,
            'values' => array(
                array(
                    'value' => 0,
                    'label' => $this->__('no')
                ),
                array(
                    'value' => 1,
                    'label' => $this->__('yes')
                )
            ),
        ));


        $fieldset->addField('ftp_host', 'text', array(
            'label' => Mage::helper('massstockupdate')->__('Host'),
            'value' => $model->getFtpHost(),
            'name' => 'ftp_host',
            'id' => 'ftp_host',
        ));

        $fieldset->addField('ftp_login', 'text', array(
            'label' => Mage::helper('massstockupdate')->__('Login'),
            'value' => $model->getFtpLogin(),
            'name' => 'ftp_login',
            'id' => 'ftp_login',
        ));
        $fieldset->addField('ftp_password', 'password', array(
            'label' => Mage::helper('massstockupdate')->__('Password'),
            'value' => $model->getFtpPassword(),
            'name' => 'ftp_password',
            'id' => 'ftp_password',
        ));
        $fieldset->addField('ftp_dir', 'text', array(
            'label' => Mage::helper('massstockupdate')->__('Directory'),
            'value' => $model->getFtpDir(),
            'name' => 'ftp_dir',
            'id' => 'ftp_dir',
            'note' => "<a style='margin:10px; display:block;' href='javascript:massstockupdate.testFtp(\"" . $this->getUrl('*/*/ftp') . "\")'>Test Connection</a>"
        ));
        
        $fieldset->addField('file_path', 'text', array(
            'name' => 'file_path',
            'value' => $model->getFilePath(),
            'label' => Mage::helper('massstockupdate')->__('Path to file'),
            "required" => true,
        ));
        
        
        $fieldset->addField('file_type', 'select', array(
            'name' => 'file_type',
            'value' => $model->getFileType(),
            'label' => Mage::helper('massstockupdate')->__('File type'),
            "required" => true,
            'values' => array(
                array(
                    'value' => 0,
                    'label' => 'CSV'
                ),
                array(
                    'value' => 1,
                    'label' => 'XML'
                )
            ),
        ));
        
        $fieldset->addField('xpath_to_product', 'text', array(
            'name' => 'xpath_to_product',
            'value' => $model->getXpathToProduct(),
            'label' => Mage::helper('massstockupdate')->__('Xpath to products'),
            "required" => true,
        ));
        
        
        $fieldset->addField('file_separator', 'select', array(
            'name' => 'file_separator',
            'value' => $model->getFileSeparator(),
            'label' => Mage::helper('massstockupdate')->__('Field separator'),
            "required" => true,
            'options' => array(
                ';' => ';',
                ',' => ',',
                '|' => '|',
                "\t" => '\tab',
            ),
        ));
        $fieldset->addField('file_enclosure', 'select', array(
            'name' => 'file_enclosure',
            'value' => $model->getFileEnclosure(),
            'label' => Mage::helper('massstockupdate')->__('Field enclosure'),
            "required" => true,
            'options' => array(
                "none" => 'none',
                '"' => '"',
                '\'' => '\'',
            ),
        ));
        $fieldset = $form->addFieldset('massstockupdate_form2', array('legend' => $this->__('Profile Options')));
        
        $fieldset->addField('sku_offset', 'text', array(
            'name' => 'sku_offset',
            'value' => ($model->getSkuOffset()) ? $model->getSkuOffset() : 1,
            "required" => true,
            "class" => "validate-number",
            'label' => Mage::helper('massstockupdate')->__('Product identifier offset'),
            'note' => 'Define the identifier\'s columnn',
        ));
        
        $attributes = Mage::getResourceModel('catalog/product_attribute_collection')->getItems();
        $options = array();
        foreach ($attributes as $attribute) {
            if ($attribute->getIsUnique()) {
                $options[$attribute->getAttributeCode()] = $attribute->getFrontendLabel();
            }
        }

        $fieldset->addField('identifier_code', 'select', array(
            'name' => 'identifier_code',
            'value' => $model->getIdentifierCode(),
            "required" => true,
            'label' => Mage::helper('massstockupdate')->__(' Product Identifier'),
            'options' => $options,
        ));

        $options = array();
        $options[] = 'set in a specific column';
        $options[] = 'sum of all specified columns';
        if (Mage::helper("core")->isModuleEnabled("Wyomind_Advancedinventory")) {
            $options[] = 'sum of all local stocks';
        }
        $fieldset->addField('auto_set_total', 'select', array(
            'name' => 'auto_set_total',
            'value' => $model->getAutoSetTotal(),
            'label' => Mage::helper('massstockupdate')->__('Total stock calculation'),
            'note' => 'Calculate the total stock for each product depending of the above method',
            'options' => $options,
        ));
        

        $fieldset->addField('auto_set_instock', 'select', array(
            'name' => 'auto_set_instock',
            'value' => $model->getAutoSetInstock(),
            'label' => Mage::helper('massstockupdate')->__('Stock status update'),
            'note' => 'Update stock status for each product depending of the above method',
            'options' => array(
                1 => 'automatically',
                0 => 'defined in a specific column',
            ),
        ));
        /* $fieldset->addField('auto_set_managestock', 'select', array(
          'name' => 'auto_set_managestock',
          'value' => $model->getAutoSetManagestock(),
          'label' => Mage::helper('massstockupdate')->__('Automatically set "Manage stock"?'),
          'note' => 'Set all updated product on "Manage stock = yes"',
          'options' => array(
          1 => 'yes',
          0 => 'no',
          ),
          )); */


        $fieldset = $form->addFieldset('massstockupdate_form3', array('legend' => $this->__('Custom rules')));

        $fieldset->addField('use_custom_rules', 'select', array(
            'name' => 'use_custom_rules',
            'value' => $model->getUseCustomRules(),
            'label' => Mage::helper('massstockupdate')->__('Use custom rules'),
            'note' => '',
            'options' => array(
                1 => 'yes',
                0 => 'no',
            ),
        ));

        $fieldset->addField('custom_rules', 'textarea', array(
            'name' => 'custom_rules',
            'value' => $model->getCustomRules(),
            'label' => Mage::helper('massstockupdate')->__('Rules'),
            'note' => '',
            'options' => array(
                1 => 'yes',
                0 => 'no',
            ),
        ));


        $fieldset->addField('mapping', $type, array(
            'name' => 'mapping',
            'value' => $model->getMapping()
        ));
        $fieldset->addField('cron_setting', $type, array(
            'name' => 'cron_setting',
            'value' => $model->getCronSetting()
        ));
        $fieldset->addField('run', $type, array(
            'name' => 'run',
            'value' => ''
        ));

        if (version_compare(Mage::getVersion(), '1.3.0', '>')) {
            
            $this->setChild('form_after', $this->getLayout()->createBlock('adminhtml/widget_form_element_dependence')
                ->addFieldMap('use_custom_rules', 'use_custom_rules')
                ->addFieldMap('custom_rules', 'custom_rules')
                ->addFieldMap('file_system_type', 'file_system_type')
                ->addFieldMap('file_type', 'file_type')
                ->addFieldMap('file_separator', 'file_separator')
                ->addFieldMap('file_enclosure', 'file_enclosure')
                ->addFieldMap('xpath_to_product', 'xpath_to_product')
                ->addFieldMap('use_sftp', 'use_sftp')
                ->addFieldMap('ftp_host', 'ftp_host')
                ->addFieldMap('ftp_login', 'ftp_login')
                ->addFieldMap('ftp_password', 'ftp_password')
                ->addFieldMap('ftp_dir', 'ftp_dir')
                ->addFieldMap('ftp_active', 'ftp_active')
                ->addFieldDependence('ftp_host', 'file_system_type', 1)
                ->addFieldDependence('use_sftp', 'file_system_type', 1)
                ->addFieldDependence('ftp_login', 'file_system_type', 1)
                ->addFieldDependence('ftp_password', 'file_system_type', 1)
                ->addFieldDependence('ftp_active', 'file_system_type', 1)
                ->addFieldDependence('ftp_active', 'use_sftp', 0)
                ->addFieldDependence('ftp_dir', 'file_system_type', 1)
                ->addFieldDependence('custom_rules', 'use_custom_rules', 1)
                ->addFieldDependence('file_enclosure', 'file_type', 0)
                ->addFieldDependence('file_separator', 'file_type', 0)
                ->addFieldDependence('xpath_to_product', 'file_type', 1));
        }
        
        






        if (Mage::getSingleton('adminhtml/session')->getMassstockupdateData()) {
            //$form->setValues(Mage::getSingleton('adminhtml/session')->getMassstockupdateData());
            Mage::getSingleton('adminhtml/session')->getMassstockupdateData(null);
        } elseif (Mage::registry('massstockupdate_data')) {
            //$form->setValues(Mage::registry('massstockupdate_data')->getData());
        }

        return parent::_prepareForm();
    }

}
