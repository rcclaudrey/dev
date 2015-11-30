<?php

class Wyomind_Pointofsale_Block_Adminhtml_Manage_Renderer_Store extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {
        $content = "";
       

        $content.= "<b>" . $row->getName() . ' [' . $row->getStoreCode() . ']</b><br>';
        $content.= Mage::helper('pointofsale')->getStoreDescription($row);

        return $content;
    }

}
