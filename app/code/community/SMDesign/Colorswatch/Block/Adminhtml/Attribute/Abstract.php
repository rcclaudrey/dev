<?php
abstract class SMDesign_Colorswatch_Block_Adminhtml_Attribute_Abstract extends Mage_Adminhtml_Block_Template {
	
	
	
	function __construct() {
		$this->setTemplate('colorswatch/attribute/accordion/content.phtml');
	}
	
	function _prepareLayout() {
		
		return parent::_prepareLayout();
	}
	
  public function _toHtml() {


  	return parent::_toHtml();
  }
}