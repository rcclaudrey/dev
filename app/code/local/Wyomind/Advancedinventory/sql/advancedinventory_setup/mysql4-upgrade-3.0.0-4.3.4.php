<?php

$installer = $this;
$installer->startSetup();

$installer->run("ALTER TABLE `{$this->getTable('advancedinventory')}` DROP FOREIGN KEY `CONST advancedinventory_place_id`;");
$installer->run("ALTER  TABLE `{$this->getTable('advancedinventory')}` ADD CONSTRAINT `CONST_advancedinventory_place_id` FOREIGN KEY (`place_id`) REFERENCES `{$this->getTable('pointofsale')}` (`place_id`) ON DELETE CASCADE ON UPDATE CASCADE;");

$installer->run("ALTER TABLE `{$this->getTable('advancedinventory')}` DROP FOREIGN KEY `CONST advancedinventory_product_id`;");
$installer->run("ALTER  TABLE `{$this->getTable('advancedinventory')}` ADD CONSTRAINT `CONST_advancedinventory_product_id` FOREIGN KEY (`product_id`) REFERENCES `{$this->getTable('catalog_product_entity')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE;");


$installer->run("ALTER TABLE `{$this->getTable('advancedinventory')}` DROP FOREIGN KEY `CONST advancedinventory_localstock_id`;");
$installer->run("ALTER  TABLE `{$this->getTable('advancedinventory')}` ADD CONSTRAINT `CONST_advancedinventory_localstock_id` FOREIGN KEY (localstock_id) REFERENCES `{$this->getTable('advancedinventory_product')}` (id) ON UPDATE CASCADE ON DELETE CASCADE;");
$installer->endSetup();