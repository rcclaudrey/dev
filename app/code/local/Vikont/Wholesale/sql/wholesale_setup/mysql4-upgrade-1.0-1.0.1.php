<?php

$this->startSetup();

$setup = Mage::getModel('customer/entity_setup', 'core_setup');

$attributes = array(
	Vikont_Wholesale_Helper_Data::ATTR_APPLICATION_DATA => array(
		'type' => 'text',
		'input' => 'textarea',
		'label' => 'Dealer Application',
		'visible' => true,
		'required' => false,
		'default' => '',
		'visible_on_front' => true,
		'backend' => 'wholesale/eav_entity_attribute_backend_json',
	),
	Vikont_Wholesale_Helper_Data::ATTR_DEALER_STATUS => array(
		'type' => 'int',
		'input' => 'select',
		'label' => 'Dealer Status',
		'source' => 'wholesale/source_dealer_status',
		'visible' => true,
		'required' => false,
		'default' => Vikont_Wholesale_Model_Source_Dealer_Status::NONE,
		'visible_on_front' => true,
	),
	Vikont_Wholesale_Helper_Data::ATTR_DEALER_COST => array(
		'type' => 'decimal',
		'input' => 'text',
		'label' => 'Dealer Cost',
		'visible' => true,
		'required' => false,
		'default' => 0,
		'visible_on_front' => true,
	),
);

$defaultUsedInForms = array('wholesale');

foreach($attributes as $attrCode => $attrData) {
	$setup->addAttribute('customer', $attrCode, $attrData);

	$attribute = Mage::getSingleton('eav/config')->getAttribute('customer', $attrCode);
	$attribute
		->setData('used_in_forms', $defaultUsedInForms)
		->save();
}

$this->run('');
$this->endSetup();