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
class Celebros_Conversionpro_Block_Adminhtml_Settings_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
	 * Load fields collection
	 *
	 * @return Celebros_Conversionpro_Model_Mysql4_Mapping_Collection
	 */
	protected function _loadFieldsCollection(){
		if(!$this->_fieldsCollection){
			$this->_fieldsCollection = Mage::getSingleton("conversionpro/mapping")->getCollection();
		}
		return $this->_fieldsCollection;
	}
	
	protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('form_form', array('legend'=>Mage::helper('conversionpro')->__('Attribute Mapping')));
		
		foreach ($this->_loadFieldsCollection() as $field) {
			$fieldset->addField($field->getCodeField(), 'text', array(
			  'label'     => $field->getCodeField(),
			  'value'     => $field->getXmlField(),
			  'class'     => 'required-entry',
			  'required'  => true,
			  'name'      => 'mapping[' . $field->getId() . ']',
			));
		}
          
        return parent::_prepareForm();
    }
}