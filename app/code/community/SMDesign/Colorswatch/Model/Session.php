<?php
class SMDesign_Colorswatch_Model_Session extends Mage_Core_Model_Session_Abstract {
	
    public function __construct() {
        $namespace = 'colorswatch';
      

        $this->init($namespace);
        Mage::dispatchEvent('colorswatch_session_init', array('colorswatch_session'=>$this));
    }
}