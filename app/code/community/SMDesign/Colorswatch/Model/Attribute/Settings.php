<?php
class SMDesign_Colorswatch_Model_Attribute_Settings extends Mage_Core_Model_Abstract {
	

	
    protected function _construct() {
        $this->_init('colorswatch/attribute_settings', array('attribute_id', 'key'));
    }
    
    function getConfig($attributeId, $key) {
    	$conf = $this->getCollection()->getConfig($attributeId, $key);
    	if (count($conf) == 1) {
    		$tmp = current($conf);
    		return isset($tmp['value']) ? $tmp['value'] : '';
    	}
    	return false;
    }
    
    function setConfig($attributeId, $key, $val, $attributeCode = '') {
    	$exsistingConf = $this->getCollection()->getConfig($attributeId, $key);
    	if (count($exsistingConf) == 1) {
    		$conf = current($exsistingConf);
    		$this->setData('entity_id', $conf['entity_id']);
    	}
    	$this->setData('attribute_id', $attributeId);
    	$this->setData('key', $key);
    	$this->setData('value', $val);
    	$this->setData('attribute_code', $attributeCode);
    	$this->save();
    }
}