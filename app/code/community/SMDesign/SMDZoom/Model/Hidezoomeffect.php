<?php 
class SMDesign_SMDZoom_Model_hidezoomeffect {
    public function toOptionArray() {
        return array(
            array('value' => 'none', 'label'=>Mage::helper('adminhtml')->__('None')),
            array('value' => 'Fade', 'label'=>Mage::helper('adminhtml')->__('Fade')),
            array('value' => 'Puff', 'label'=>Mage::helper('adminhtml')->__('Puff')),
            array('value' => 'BlindDown', 'label'=>Mage::helper('adminhtml')->__('Blind Up')),
            array('value' => 'SlideUp', 'label'=>Mage::helper('adminhtml')->__('Slide Up')),
            array('value' => 'DropOut', 'label'=>Mage::helper('adminhtml')->__('DropOut')),
            array('value' => 'Shrink', 'label'=>Mage::helper('adminhtml')->__('Shrink')),
        );
    }
}