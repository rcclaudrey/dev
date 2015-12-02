<?php

$installer = $this;
$installer->startSetup();

$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
/**
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
));/**/

$setup->addAttribute(Mage_Sales_Model_Order::ENTITY, Vikont_EVOConnector_Helper_Data::ORDER_EVO_STATUS_FIELD, array(
        'type' => 'static', // varchar
		'required' => 0,
		'label' => 'EVO Order State',
		'default' => 0,
		'visible' => false,
		'input' => 'text',
		'global' => 1,
    )
);

$setup->getConnection()->addColumn($setup->getTable('sales_flat_order'),
		Vikont_EVOConnector_Helper_Data::ORDER_EVO_STATUS_FIELD,
		'TINYINT(1) UNSIGNED NOT NULL DEFAULT 0'
	);

$setup->run("UPDATE `{$setup->getTable('sales_flat_order')}` SET " . Vikont_EVOConnector_Helper_Data::ORDER_EVO_STATUS_FIELD . "=1 WHERE status<>'pending'");

$installer->run('');
$installer->endSetup();