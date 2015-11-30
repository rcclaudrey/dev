<?php

class Wyomind_Massstockupdate_Block_Adminhtml_Import_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {

    public function __construct() {

        parent::__construct();

        $this->_objectId = 'profile_id';
        $this->_controller = 'adminhtml_import';
        $this->_blockGroup = 'massstockupdate';


        if (Mage::registry('massstockupdate_data')->getProfileId()) {
            $this->_addButton('import', array(
                'label' => Mage::helper('adminhtml')->__('Run profil now'),
                'onclick' => 'if(confirm(\''.Mage::helper('massstockupdate')->__('All your stock will be updated. Continue ?').'\')){$(\'run\').value=1; editForm.submit();}',
                'class' => 'add',
            ));
           
        }
    }

    public function getHeaderText() {
        if (Mage::registry('massstockupdate_data') && Mage::registry('massstockupdate_data')->getProfileId()) {
            return Mage::helper('massstockupdate')->__("Edit profile  '%s'", $this->htmlEscape(Mage::registry('massstockupdate_data')->getProfile_name()));
        } else {
            return Mage::helper('massstockupdate')->__('New profile');
        }
    }

}