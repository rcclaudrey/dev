<?php

class SMDesign_SMDZoom_Helper_Data extends Mage_Core_Helper_Abstract {

	protected $_model;
	
	function setModel($model) {
		$this->_model = $model;
	}
	
	function getModel() {
		return $this->_model;
	}
	
}
?>