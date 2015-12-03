<?php
/**
 * Celebros Qwiser - Magento Extension
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
class Celebros_Conversionpro_Helper_Mapping extends Mage_CatalogSearch_Helper_Data
{

	/**
	 * Retrieve Mapping array
	 *
	 * @return array
	 */
	public function getMappingFieldsArray(){
		return Mage::getSingleton("conversionpro/mapping")->getFieldsArray();
	}
	
 	/**
	 * Retrieve a mapping for a field
	 * 
	 * @return string
	 * 
	 */
	public function getMapping($code_field = ""){
		$mappingArray = $this->getMappingFieldsArray();
		if(!key_exists($code_field, $mappingArray)){
			return $code_field;
		}
		return strtolower($mappingArray[$code_field]);
	}
}