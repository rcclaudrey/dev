<?php

class Wyomind_Pointofsale_Block_Adminhtml_Manage_Renderer_Order extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {
        
        
        
        $content="<input type='text' size='3' value='".$row->getOrder()."' name='order[".$row->getStoreId()."]'/>";
        return $content;
    }

}
