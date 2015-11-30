<?php

class Wyomind_Pointofsale_Block_Adminhtml_Manage_Renderer_Customergroup extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {
        $_customer_group = array();
        $output=null;
        $customer_group = new Mage_Customer_Model_Group();
        $allGroups = $customer_group->getCollection()->toOptionHash();
        
        foreach ($allGroups as $key => $allGroup) {
            $_customer_group[$key] =  $allGroup;
        }
        $selection=explode(',',$row->getCustomerGroup());
       
        if(in_array('-1',$selection) || count($selection)<1) {
            echo Mage::helper('pointofsale')->__("No Customer Group");
            return;
        }
        else{
            foreach($selection as $v){
                $output.=$_customer_group[$v]."<br>";
            }
        }
        echo $output;
        return ;
    }
    
    
}
