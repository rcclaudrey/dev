<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Rma
 */ 
class Amasty_Rma_Block_System_Config_Form_Field_Simple extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    public function __construct()
    {
        $this->addColumn('value', array(
            'label' => Mage::helper('adminhtml')->__('Value'),
            'style' => 'width:120px',
        ));
        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('adminhtml')->__('Add Value');
        parent::__construct();
    }
    
    public function getArrayRows()
    {
        
        $result = array();
        /** @var Varien_Data_Form_Element_Abstract */
        $element = $this->getElement();
        
        if ($element->getValue() && is_array($element->getValue())) {
            
            foreach ($element->getValue() as $rowId => $row) {
                foreach ($row as $key => $value) {
                    $row[$key] = $this->htmlEscape($value);
                }
                $row['_id'] = $rowId;
                $result[$rowId] = new Varien_Object($row);
                $this->_prepareArrayRow($result[$rowId]);
            }
        }
        
        return $result;
    }
}
