<?php

class SMDesign_Colorswatch_Helper_Data extends Mage_Core_Helper_Abstract {
	private $license_errors;
	
	public function setError($error) {
		$this->license_errors[] = $error;
	}
	
	public function hasError() {
		return count($this->license_errors) > 0 ? true : false;
	}
	
	public function getError() {
		return current($this->license_errors);
	}
}