<?php
/**
 * Celebros Qwiser - Magento Extension
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish correct extension functionality.
 * If you wish to customize it, please contact Celebros.
 *
 * @category    Celebros
 * @package     Celebros_Conversionpro
 * @author		Shay Acrich (email: me@shayacrich.com)
 *
 */
class Celebros_Conversionpro_Block_Adminhtml_Settings_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
 
  public function __construct()
  {
      parent::__construct();
      $this->setId('form_tabs');
      $this->setDestElementId('edit_form'); // this should be same as the form id define above
      $this->setTitle(Mage::helper('conversionpro')->__('ConversionPro Settings'));
  }
 
  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('conversionpro')->__('Attribute Mapping'),
          'title'     => Mage::helper('conversionpro')->__('Attribute Mapping'),
          'content'   => $this->getLayout()->createBlock('conversionpro/adminhtml_settings_edit_tab_form')->toHtml(),
      ));
	  
      return parent::_beforeToHtml();
  }
}