<?php

class SMDesign_Colorswatch_Model_Mysql4_Swatch_Images_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {
	
    public function _construct() {
        parent::_construct();
        $this->_init('colorswatch/swatch_images', 'entity_id');
    }
    
    public function addSelectionByProducts(array $products) {
    	$tableName = Mage::getSingleton('core/resource')->getTableName('catalog_product_super_attribute');
    	
    	$select = $this->getConnection()->select()
    		 ->from(array('main_table'=>$tableName), array('product_super_attribute_id', 'attribute_id'))
    		 ->where('main_table.product_id in (?)', $products)
    		 ->group('main_table.attribute_id');
    	$_SuperAttribute = $this->getConnection()->fetchPairs($select);
    	
    	if (count($_SuperAttribute) > 0) {	 
    		 $this->getSelect()
    		 	 ->where('main_table.attribute_id in (?)', $_SuperAttribute);
    	}
			return $this;
    }
    
}