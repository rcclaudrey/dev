<?php
class SMDesign_Colorswatch_Block_Adminhtml_Header extends Mage_Adminhtml_Block_Widget_Container {
	
	public $_headerText;
	
	function __construct() {
		parent::__construct();
		
		if (!$this->hasData('template')) {
			$this->setTemplate('colorswatch/header.phtml');
		}
		
		$this->_headerText = Mage::helper('colorswatch')->__('Welcome to SMDesign ColorSwatch');
	}
	
  public function getHeaderHtml() {
  
      return '<h3 class="' . $this->getHeaderCssClass() . '">' . $this->getHeaderText() . '</h3>';
  }
  

    

}