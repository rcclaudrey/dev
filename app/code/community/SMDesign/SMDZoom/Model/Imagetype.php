<?php 
class SMDesign_SMDZoom_Model_Imagetype {


    public function toOptionArray() {
        return array(
            array('value' => 1, 'label'=>Mage::helper('adminhtml')->__('Orginal uploaded images')),
            array('value' => 2, 'label'=>Mage::helper('adminhtml')->__('Resize by using the container dimensions and zoom ratio')),
//          array('value' => 4, 'label'=>Mage::helper('adminhtml')->__('Detect Image size using Java Script')),
        );
    }

}