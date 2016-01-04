<?php

$installer = $this;
$installer->startSetup();
$installer->run("
        DROP TABLE IF EXISTS `{$this->getTable('advancedinventory_item')}`;
        CREATE TABLE `{$this->getTable('advancedinventory_item')}` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `product_id` int(10) NOT NULL,
        `manage_local_stock` int(1) NOT NULL DEFAULT 0,
        PRIMARY KEY (`id`),
        UNIQUE KEY `UNIQ product` (`product_id`),
        CONSTRAINT CONST_product_id FOREIGN KEY (product_id) REFERENCES `{$this->getTable('catalog_product_entity')}` (entity_id) ON UPDATE CASCADE ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
  ");
       
$installer->run("       
        DROP TABLE IF EXISTS {$this->getTable('advancedinventory_stock')};
        CREATE TABLE `{$this->getTable('advancedinventory_stock')}` (
        `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `product_id` int(10) NOT NULL,
        `localstock_id` int(11) NOT NULL,
        `place_id` int(11) NOT NULL,
        `manage_stock`  INT(1) NOT NULL DEFAULT '1',
        `quantity_in_stock` int(8) DEFAULT '0',
        `backorder_allowed` int(1),
        `use_config_setting_for_backorders` tinyint(1) NOT NULL DEFAULT '1',
        PRIMARY KEY (`id`),
        UNIQUE KEY `UNIQ product_place` (`localstock_id`,`place_id`,`product_id`),
        KEY `CONST_product_id` (`product_id`),
        KEY `CONST_place_id` (`place_id`),
        KEY `CONST_localstock_id` (`localstock_id`),
        CONSTRAINT `CONST_advancedinventory_product_id`     FOREIGN KEY (`product_id`)      REFERENCES `{$this->getTable('catalog_product_entity')}` (entity_id) ON UPDATE CASCADE ON DELETE CASCADE,
        CONSTRAINT `CONST_advancedinventory_place_id`       FOREIGN KEY (`place_id`)        REFERENCES `{$this->getTable('pointofsale')}` (`place_id`) ON DELETE CASCADE ON UPDATE CASCADE,
        CONSTRAINT `CONST_advancedinventory_localstock_id`  FOREIGN KEY (`localstock_id`)   REFERENCES `{$this->getTable('advancedinventory_item')}` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
  ");
$installer->run("       
        DROP TABLE IF EXISTS {$this->getTable('advancedinventory_log')};
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

 ");


// ajout du champ delivery_rules dans 
$installer->getConnection()->addColumn(
        $installer->getTable('pointofsale'), 'inventory_assignation_rules', "varchar(400) DEFAULT '*'"
);
$installer->getConnection()->addColumn(
        $installer->getTable('pointofsale'), 'inventory_notification', "varchar(400) DEFAULT ''"
);
$installer->getConnection()->addColumn(
        $installer->getTable('pointofsale'), 'use_assignation_rules', "int(1) DEFAULT '0'"
);
// ajout du champ assignation dans les commandes
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
Mage::getConfig()->saveConfig("advancedinventory/setting/order_notification_from_date", Mage::getSingleton('core/date')->gmtDate('l Y-m-d H:i:s'), "default", "0");

Mage::getConfig()->saveConfig("cataloginventory/options/can_subtract", 0, "default", "0");
$installer->endSetup();
