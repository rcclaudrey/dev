<?php

class Wyomind_Massstockupdate_Block_Adminhtml_Import_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {

    public function __construct() {
        parent::__construct();
        $this->setId('massstockupdate_import');
        $this->setDestElementId('edit_form');
        $this->setTitle('Mass Stock Update');
    }

    protected function _beforeToHtml() {
        $this->addTab('form_setting', array(
            'label' => $this->__('❶ Profile setting'),
            'title' => $this->__('❶ Profile setting'),
            'content' => $this->getLayout()
                    ->createBlock('massstockupdate/adminhtml_import_edit_tab_setting')
                    ->toHtml()
        ));
        $this->addTab('form_mapping', array(
            'label' => $this->__('❷ File mapping'),
            'title' => $this->__('❷ File mapping'),
            'content' => $this->getLayout()
                    ->createBlock('massstockupdate/adminhtml_import_edit_tab_mapping')
                    ->toHtml()
        ));
        $this->addTab('form_template', array(
            'label' => $this->__('❸ Scheduled tasks'),
            'title' => $this->__('❸ Scheduled tasks'),
            'content' => $this->getLayout()
                    ->createBlock('massstockupdate/adminhtml_import_edit_tab_cron')
                    ->toHtml()
        ));
          

       
        return parent::_beforeToHtml();
    }

}
