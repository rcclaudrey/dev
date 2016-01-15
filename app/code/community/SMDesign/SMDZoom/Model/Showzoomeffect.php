<?php 
class SMDesign_SMDZoom_Model_showzoomeffect {
    public function toOptionArray() {
        return array(
        	array('value' => 'none', 'label'=>Mage::helper('adminhtml')->__('None')),
            array('value' => 'Appear', 	'label'=>Mage::helper('adminhtml')->__('Appear')),
            array('value' => 'Grow', 	'label'=>Mage::helper('adminhtml')->__('Grow')),
            array('value' => 'BlindDown','label'=>Mage::helper('adminhtml')->__('Blind Down')),
            array('value' => 'SlideDown','label'=>Mage::helper('adminhtml')->__('Slide Down')),
        );
    }
}