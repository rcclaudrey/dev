<?php

class Wyomind_Advancedinventory_Block_Adminhtml_System_Config_Form_Field_Date extends Mage_Adminhtml_Block_System_Config_Form_Field {

    public function render(Varien_Data_Form_Element_Abstract $element) {
        $element->setFormat(Varien_Date::DATE_INTERNAL_FORMAT); //or other format
        $element->setImage($this->getSkinUrl('images/grid-cal.gif'));
        return parent::render($element);
    }

}

