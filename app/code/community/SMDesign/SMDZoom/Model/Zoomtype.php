<?php 
class SMDesign_SMDZoom_Model_Zoomtype {
    public function toOptionArray() {
        return array(
            array('value' => 0, 'label'=>Mage::helper('adminhtml')->__('Outside')),
            array('value' => 1, 'label'=>Mage::helper('adminhtml')->__('Inside')),
            array('value' => 2, 'label'=>Mage::helper('adminhtml')->__('Inside Full')),
        );
    }
}