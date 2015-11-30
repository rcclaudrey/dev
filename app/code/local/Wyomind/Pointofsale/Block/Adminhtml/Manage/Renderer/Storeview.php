<?php

class Wyomind_Pointofsale_Block_Adminhtml_Manage_Renderer_Storeview extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {
        $storeViewIds=explode(',',$row->getStoreId());
     
        $websites = Mage::app()->getWebsites();
       
        if($storeViewIds[0]==0 || count($storeViewIds)<1) {
            echo Mage::helper('pointofsale')->__("No Store View");
            return;
        }
        foreach ($websites as $website) {
            if($this->in_array($storeViewIds,$website->getStoreIds())){
                echo "<div style='float:left;  padding:0 5px'><b><u>".$website->getName()."</u></b><br>";
                $storegroups = $website->getGroupCollection();
                foreach ($storegroups as $storegroup) {
                    if($this->in_array($storeViewIds,$storegroup->getStoreIds())){
                         echo "<b style='padding-left:5px;'>".$storegroup->getName()."</b><br>";
                        $storeviews = $storegroup->getStoreCollection();
                        foreach ($storeviews as $storeview) {
                          if(in_array($storeview->getId(),$storeViewIds)) echo "<span style='padding-left:10px;'>".$storeview->getName()."</span><br>";
                        }
                    }
                }
                echo "<div>";
            }
        }
    }
    public function in_array($array_1,$array_2){
        foreach($array_1 as $value){
            if(in_array($value,$array_2)) return true;
        }
        return false;
    }
    
}
