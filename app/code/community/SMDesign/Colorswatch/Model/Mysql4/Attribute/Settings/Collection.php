<?php

class SMDesign_Colorswatch_Model_Mysql4_Attribute_Settings_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {
	
    public function _construct() {
        parent::_construct();
        $this->_init('colorswatch/attribute_settings', 'entity_id');
    }
    
    public function getConfig($attributeId, $key) {
    	$tableName = Mage::getSingleton('core/resource')->getTableName('colorswatch_attribute_settings');
    	
    	$select = $this->getConnection()->select()
    		 ->from(array('main_table'=>$tableName), array('entity_id', 'attribute_id', 'key', 'value'))
    		 ->where('main_table.attribute_id=?', $attributeId)
    		 ->where('main_table.key=?', trim($key))
    		 ;
    	return $this->getConnection()->fetchAll($select);
    }
    
}