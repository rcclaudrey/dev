<?php 
/**
 * Celebros Conversion Pro - Magento Extension
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish correct extension functionality.
 * If you wish to customize it, please contact Celebros.
 *
 * @category    Celebros
 * @package     Celebros_Conversionpro
 * @author		Shay Acrich (email: me@shayacrich.com)
 *
 */
class Celebros_Conversionpro_Model_Mapping extends Mage_Core_Model_Abstract 
{
	private $_fieldsArray; 
	
    protected function _construct()
    {
        $this->_init('conversionpro/mapping');
    }   
    
    protected function _loadFieldsArray(){
    	$fieldsCollection = Mage::getSingleton("conversionpro/mapping")->getCollection();
    	$this->_fieldsArray = array();
    	foreach($fieldsCollection as $field){
    		$this->_fieldsArray[$field->getCodeField()] = $field->getXmlField();
    	}
    }
    
    /**
     * Get Fields Array 
     * 
     * @return array
     */
    public function getFieldsArray(){
    	if(!$this->_fieldsArray){
    		$this->_loadFieldsArray();
    	}
    	return $this->_fieldsArray;
    }
    
} 