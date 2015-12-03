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
class Celebros_Conversionpro_Model_System_Config_Source_Fileftp
{
    public function toOptionArray()
    {
    	return array(
            array('value' => 'file', 'label'=>Mage::helper('conversionpro')->__('File')),
            array('value' => 'ftp', 'label'=>Mage::helper('conversionpro')->__('FTP')),
        );
    }
}