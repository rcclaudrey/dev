<?php

class Wyomind_Pointofsale_Block_Adminhtml_Manage_Import_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    
    
  public function __construct()
  {
      parent::__construct();
      $this->setId('pointofsale_import');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('pointofsale')->__(''));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('uploader', array(
          'label'     => Mage::helper('pointofsale')->__('File importation'),
          'title'     => Mage::helper('pointofsale')->__('File importation'),
          'content'   => $this->getLayout()->createBlock('pointofsale/adminhtml_manage_import_tab_uploader')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}