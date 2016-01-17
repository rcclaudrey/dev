<?php

class Wyomind_Simplegoogleshopping_Block_Adminhtml_Simplegoogleshopping_Renderer_Link extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {
        return Mage::helper('simplegoogleshopping')->generationStats($row);
    }

}
