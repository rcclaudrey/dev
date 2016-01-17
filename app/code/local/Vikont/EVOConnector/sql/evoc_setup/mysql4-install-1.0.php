<?php

$installer = $this;
$installer->startSetup();

//$installer->addAttribute(
//    'order',
//    'tax_rate_id',
//    array(
//        'type' => 'static', // varchar
//        // 'default' => '',
//		'visible' => false,
//    )
//);

$installer->run('');
//
// $installer->getConnection()->addColumn($installer->getTable('sales_order'), 'protect_code', 'VARCHAR( 6 ) NULL DEFAULT NULL');
// $installer->addAttribute('order', 'protect_code', array('type'=>'static'));
// $installer->run("UPDATE `{$installer->getTable('sales_order')}` SET protect_code = SUBSTRING(MD5(CONCAT(RAND(), DATE_FORMAT(NOW(), '%H %k %I %r %T %S'), RAND())), 5, 6) WHERE protect_code IS NULL");

// $installer->getConnection()->addColumn($this->getTable('sales_order'), 'discount_refunded', 'decimal(12,4) default NULL AFTER `subtotal_canceled`');
// $installer->addAttribute('order', 'discount_refunded', array('type'=>'static'));

$installer->endSetup();