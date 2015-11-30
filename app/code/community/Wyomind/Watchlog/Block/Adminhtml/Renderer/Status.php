<?php


class Wyomind_Watchlog_Block_Adminhtml_Renderer_Status extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {
    
    public function render(Varien_Object $row) {
        
        if ($row->getType() == 1) {
            return "<span class='grid-severity-notice' title='".$row->getUseragent()."'><span>".Mage::helper('watchlog')->__("Success")."</span></span>";
        } else if ($row->getType() == 2) {
            return "<span class='grid-severity-minor' title='".$row->getUseragent()."'><span>".Mage::helper('watchlog')->__("Blocked")."</span></span>";
        } else {
            return "<span class='grid-severity-critical' title='".$row->getUseragent()."'><span>".Mage::helper('watchlog')->__("Failed")."</span></span>";
        }
    }
    
}
