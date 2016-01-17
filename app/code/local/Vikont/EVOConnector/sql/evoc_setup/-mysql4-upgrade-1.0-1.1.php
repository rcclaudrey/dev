<?php

$installer = $this;
$installer->startSetup();

$setup = new Mage_Eav_Model_Entity_Setup('core_setup');

$setup->addAttribute('order', Vikont_EVOConnector_Helper_Data::ORDER_EVO_STATUS_FIELD, array(
	'position'      => 1,
	'input'         => 'text',
	'type'          => 'tinyint',
	'label'         => 'EVO Order State',
	'visible'       => 0,
	'required'      => 0,
	'user_defined'	=> 0,
	'global'        => 1,
	'default'		=> 0,
	'visible_on_front'  => 0,
));

$installer->getConnection()->addColumn($installer->getTable('sales_flat_order'),
		Vikont_EVOConnector_Helper_Data::ORDER_EVO_STATUS_FIELD,
		'TINYINT UNSIGNED NOT NULL DEFAULT 0'
	);

$installer->run('');
$installer->endSetup();