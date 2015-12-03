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
class Celebros_Conversionpro_Model_System_Config_Source_Navigationtosearch
{
    public function toOptionArray()
    {
    	return array(
    		array('value' => 'answer_id', 'label'=>Mage::helper('conversionpro')->__('Answer Ids')),
            array('value' => 'textual', 'label'=>Mage::helper('conversionpro')->__('Textual Queries'))
        );
    }
}