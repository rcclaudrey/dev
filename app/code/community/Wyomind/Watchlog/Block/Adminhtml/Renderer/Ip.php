<?php


class Wyomind_Watchlog_Block_Adminhtml_Renderer_Ip extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {
    
    public function render(Varien_Object $row) {
        
        $ip = $row->getIp();
        
        return "<a target='_blank' href='http://www.abuseipdb.com/check/".$ip."' title='".$this->__('Check this ip')."'>".$ip."</a>";
        
    }
    
}
