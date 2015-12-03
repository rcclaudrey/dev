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
class Celebros_Conversionpro_Block_Adminhtml_Settings_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                  
        $this->_objectId = 'id';
        $this->_blockGroup = 'conversionpro';
        $this->_controller = 'adminhtml_settings';
         
        $this->_updateButton('save', 'label', Mage::helper('conversionpro')->__('Save'));
    }
 
    public function getHeaderText()
    {
        return Mage::helper('conversionpro')->__('Attribute Mapping');
    }
}