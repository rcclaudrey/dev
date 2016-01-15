<?php

class SMDesign_ColorswatchProductView_Model_System_Config_Source_Swatches_Load_Options {

	public function toOptionArray() {
		return array(
			array('value' => 0, 'label'=>Mage::helper('colorswatchproductview')->__('Load all swatches')),
			array('value' => 1, 'label'=>Mage::helper('colorswatchproductview')->__('Show only available swatches')),
		);
	}
}