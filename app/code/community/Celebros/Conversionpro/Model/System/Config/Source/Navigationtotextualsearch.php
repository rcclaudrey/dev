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
 * @author		Omniscience Co. - Dan Aharon-Shalom (email: dan@omniscience.co.il)
 *
 */
class Celebros_Conversionpro_Model_System_Config_Source_Navigationtotextualsearch
{
    public function toOptionArray()
    {
    	return array(
    		array('value' => 'category', 'label'=>Mage::helper('conversionpro')->__('Category name')),
            array('value' => 'full_path', 'label'=>Mage::helper('conversionpro')->__('Full category path')),
    		array('value' => 'category_and_parent', 'label'=>Mage::helper('conversionpro')->__('Category and category parent name')),    			
    		array('value' => 'category_and_root', 'label'=>Mage::helper('conversionpro')->__('Category and category root name')),
        );
    }
}