<?php
 
$installer = $this;
$installer->startSetup();

$installer->run("ALTER TABLE `{$this->getTable('advancedinventory_product')}` DROP total_quantity_in_stock;");

$installer->run("
ALTER TABLE `{$this->getTable('advancedinventory')}`
ADD manage_stock INT(1) NOT NULL DEFAULT '1' AFTER quantity_in_stock;

CREATE TABLE `{$this->getTable('advancedinventory_log')}` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
       `datetime` datetime DEFAULT NULL,
        `user` varchar(60) DEFAULT NULL,
        `context` varchar(60) DEFAULT NULL,
        `action` varchar(60) DEFAULT NULL,
        `reference` varchar(100) DEFAULT NULL,
        `details` text,
        PRIMARY KEY (`id`)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
      
ALTER TABLE `{$this->getTable('advancedinventory')}` RENAME `{$this->getTable('advancedinventory_stock')}`;
ALTER TABLE `{$this->getTable('advancedinventory_product')}` RENAME `{$this->getTable('advancedinventory_item')}`;
");

$installer->getConnection()->addColumn(
        $installer->getTable('pointofsale'), 'use_assignation_rules', "int(1) DEFAULT '0'"
);
if (version_compare(Mage::getVersion(), '1.4.0', '<')) {
    $installer->getConnection()->addColumn(
            $installer->getTable('sales_order'), 'assignation_stock', 'VARCHAR(1500) DEFAULT NULL'
    );

    $installer->getConnection()->addColumn(
            $installer->getTable('sales_order'), 'assignation_warehouse', 'VARCHAR(500) DEFAULT 0'
    );
    $setup = new Mage_Eav_Model_Entity_Setup('core_setup');
    $setup->addAttribute('order', 'assignation_stock', array('type' => 'static', 'visible' => false));
    $setup->addAttribute('order', 'assignation_warehouse', array('type' => 'static', 'visible' => false));
} else {
    $installer->getConnection()->addColumn(
            $installer->getTable('sales_flat_order'), 'assignation_stock', 'VARCHAR(1500) DEFAULT NULL'
    );
    $installer->getConnection()->addColumn(
            $installer->getTable('sales/order_grid'), 'assignation_stock', 'VARCHAR(1500) DEFAULT NULL'
    );

    $installer->getConnection()->addColumn(
            $installer->getTable('sales_flat_order'), 'assignation_warehouse', 'VARCHAR(500) DEFAULT 0'
    );
    $installer->getConnection()->addColumn(
            $installer->getTable('sales/order_grid'), 'assignation_warehouse', 'VARCHAR(500) DEFAULT 0'
    );
}
Mage::getConfig()->saveConfig("cataloginventory/options/can_subtract", 0, "default", "0");
$installer->endSetup();
