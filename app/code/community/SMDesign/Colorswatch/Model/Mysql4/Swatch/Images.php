<?php
class SMDesign_Colorswatch_Model_Mysql4_Swatch_Images extends Mage_Core_Model_Mysql4_Abstract {
	
    public function _construct() {    
        $this->_init('colorswatch/swatch_images', 'entity_id');
    }
    
    public function getId() {
    	return ($this->getData('entity_id') > 0  ? $this->getData('entity_id') : 0);
    }
}