<?php
abstract class SMDesign_Colorswatch_Block_Adminhtml_Abstract extends Mage_Adminhtml_Block_Template {
	
	protected $_headerText;
	protected $_headerButtons;
	
	function __construct() {
		
	}
	
	function setHeaderText($text) {
		$this->_headerText = $text;
	}
	
  
  public function _toHtml() {

  	$header = $this->getLayout()->createBlock('colorswatch/adminhtml_header');
  	if ($this->_headerText) {
  		$header->_headerText = $this->_headerText;
  	}
  	if ($this->_headerButtons) {
  		foreach ($this->_headerButtons as $key=>$property) {
        $header->addButton($key, $property);
  		}
  	}


  	return $header->toHtml() . parent::_toHtml();
  }
  
  
  function addButton($key, $property) {
  	$this->_headerButtons[$key] = $property;
  	
  }
}