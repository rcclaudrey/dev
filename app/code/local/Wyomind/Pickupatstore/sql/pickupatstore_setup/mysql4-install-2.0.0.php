<?php

$installer = $this;
$installer->startSetup();

if (version_compare(Mage::getVersion(), '1.5.0', '>='))
    $setup = new Mage_Sales_Model_Resource_Setup('core_setup');
else
    $setup = new Mage_Eav_Model_Entity_Setup('core_setup');

$installer->getConnection()->addColumn($installer->getTable('sales_flat_quote'), 'pickup_datetime', 'DATETIME');

$setup->addAttribute('quote', 'pickup_datetime', array('type' => 'static', 'visible' => false));

$installer->run("ALTER TABLE {$this->getTable('sales_flat_order')} 
  MODIFY  `shipping_description` varchar(1500);
");


$installer->getConnection()->addColumn($installer->getTable('sales_flat_order'), 'pickup_datetime', 'DATETIME');
$setup->addAttribute('order', 'pickup_datetime', array('type' => 'static', 'visible' => false));

$installer->getConnection()->addColumn($installer->getTable('sales_flat_order_grid'), 'pickup_datetime', 'DATETIME');
$setup->addAttribute('order', 'pickup_datetime', array('type' => 'static', 'visible' => false));


$installer->endSetup();
