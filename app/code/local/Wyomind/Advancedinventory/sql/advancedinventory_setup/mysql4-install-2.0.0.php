<?php

if (Mage::helper('core')->isModuleEnabled('Wyomind_Pointofsale')) {
    $installer = $this;
    $installer->startSetup();
    $installer->run("
DROP TABLE IF EXISTS `{$this->getTable('advancedinventory_product')}`;
CREATE TABLE `{$this->getTable('advancedinventory_product')}` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(10) DEFAULT '0',
  `manage_local_stock` int(1) NOT NULL DEFAULT 0,
  `total_quantity_in_stock` int(8) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ product` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        
DROP TABLE IF EXISTS {$this->getTable('advancedinventory')};
CREATE TABLE `{$this->getTable('advancedinventory')}` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `localstock_id` int(11) NOT NULL,
  `place_id` int(11) NOT NULL,
  `quantity_in_stock` int(8) DEFAULT '0',
  `product_id` int(10) unsigned NOT NULL DEFAULT '0',
  `backorder_allowed` int(1),
  `use_config_setting_for_backorders` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ product_place` (`product_id`,`place_id`),
  KEY `CONST place_id` (`place_id`),
  KEY `CONST product_id` (`product_id`),
  KEY `localstock_id` (`localstock_id`),
  CONSTRAINT `CONST advancedinventory_place_id` FOREIGN KEY (`place_id`) REFERENCES `{$this->getTable('pointofsale')}` (`place_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `CONST advancedinventory_product_id` FOREIGN KEY (`product_id`) REFERENCES `{$this->getTable('catalog_product_entity')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `CONST advancedinventory_localstock_id` FOREIGN KEY (`localstock_id`) REFERENCES `{$this->getTable('advancedinventory_product')}` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
 ");
// ajout du champ delivery_rules dans localstores
    $installer->getConnection()->addColumn(
            $installer->getTable('pointofsale'), 'inventory_assignation_rules', "varchar(400) DEFAULT '*'"
    );
    $installer->getConnection()->addColumn(
            $installer->getTable('pointofsale'), 'inventory_notification', "varchar(400) DEFAULT ''"
    );
// ajout du champ assignation dans les commandes
    if (version_compare(Mage::getVersion(), '1.4.0', '<')) {
        $installer->getConnection()->addColumn(
                $installer->getTable('sales_order'), 'assignation', 'INT(11) unsigned NOT NULL DEFAULT 0'
        );
        $setup = new Mage_Eav_Model_Entity_Setup('core_setup');
        $setup->addAttribute('order', 'assignation', array('type' => 'static', 'visible' => false));
    } else {
        $installer->getConnection()->addColumn(
                $installer->getTable('sales_flat_order'), 'assignation', 'INT(11) unsigned NOT NULL DEFAULT 0'
        );
        $installer->getConnection()->addColumn(
                $installer->getTable('sales/order_grid'), 'assignation', 'INT(11) unsigned NOT NULL DEFAULT 0'
        );
    }
    $installer->run("
 DROP TABLE IF EXISTS {$this->getTable('advancedinventory_import')};
 CREATE TABLE {$this->getTable('advancedinventory_import')} (
  `profile_id` int(11) NOT NULL AUTO_INCREMENT,
  `profile_name` varchar(100) DEFAULT NULL,
  `file_path` varchar(250) DEFAULT NULL,
  `file_separator` varchar(4) DEFAULT ';',
  `file_enclosure` varchar(4) DEFAULT ';',
  `update_method` int(1) DEFAULT 1,
  `mapping` text,
  `cron_setting` text ,
  `imported_at` datetime DEFAULT NULL,
  PRIMARY KEY (`profile_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");
    if (strpos($_SERVER['HTTP_HOST'], "wyomind.com")) {
        $installer->run(
                "UPDATE `sales_flat_order_grid` SET assignation=273 WHERE entity_id=15;
             UPDATE `sales_flat_order_grid` SET assignation=539 WHERE entity_id=16;
             UPDATE `sales_flat_order` SET assignation=273 WHERE entity_id=15;
             UPDATE `sales_flat_order` SET assignation=539 WHERE entity_id=16;"
        );
        $installer->run(
                "INSERT INTO `advancedinventory` (`id`, `localstock_id`, `place_id`, `quantity_in_stock`, `product_id`, `backorder_allowed`, `use_config_setting_for_backorders`) VALUES
            (2, 1, 539, 46, 36, NULL, 1),
            (4, 2, 539, 0, 96, NULL, 1),
            (6, 3, 539, 0, 139, NULL, 1),
            (8, 4, 539, 0, 92, NULL, 1),
            (10, 5, 539, 10, 134, NULL, 1),
            (12, 6, 539, 500, 33, NULL, 1),
            (14, 7, 539, 554, 89, NULL, 1),
            (16, 8, 539, 5, 131, NULL, 1),
            (18, 9, 539, 532, 30, NULL, 1),
            (20, 10, 539, 0, 86, NULL, 1),
            (22, 11, 539, 0, 128, NULL, 1),
            (24, 12, 539, 565, 27, NULL, 1),
            (26, 13, 539, 52, 82, NULL, 1),
            (28, 14, 539, 542, 124, NULL, 1),
            (30, 15, 539, 0, 166, NULL, 1),
            (32, 16, 539, 450, 20, NULL, 1),
            (34, 17, 539, 0, 79, NULL, 1),
            (36, 18, 539, 25, 118, NULL, 1),
            (38, 19, 539, 0, 160, NULL, 1),
            (40, 20, 539, 0, 17, NULL, 1),
            (41, 17, 273, 0, 79, NULL, 1),
            (42, 21, 273, 14, 28, NULL, 1),
            (43, 21, 539, 17, 28, NULL, 1),
            (44, 22, 273, 310, 87, NULL, 1),
            (45, 22, 539, 312, 87, NULL, 1),
            (46, 23, 273, 22, 129, NULL, 1),
            (47, 23, 539, 23, 129, NULL, 1),
            (48, 24, 273, -7, 25, NULL, 1),
            (49, 24, 539, -7, 25, NULL, 1),
            (50, 25, 273, 429, 84, NULL, 1),
            (51, 25, 539, 430, 84, NULL, 1),
            (52, 26, 273, 211, 125, NULL, 1),
            (53, 26, 539, 211, 125, NULL, 1),
            (54, 27, 273, 5, 18, NULL, 1),
            (55, 27, 539, 3, 18, NULL, 1),
            (56, 28, 273, 6, 80, NULL, 1),
            (57, 28, 539, 2, 80, NULL, 1),
            (58, 29, 273, 406, 121, NULL, 1),
            (59, 29, 539, 406, 121, NULL, 1),
            (60, 30, 273, 104, 161, NULL, 1),
            (61, 30, 539, 104, 161, NULL, 1),
            (62, 31, 273, 0, 74, NULL, 1),
            (63, 31, 539, 0, 74, NULL, 1),
            (64, 32, 273, 171, 115, NULL, 1),
            (65, 32, 539, 173, 115, NULL, 1),
            (66, 33, 273, 281, 157, NULL, 1),
            (67, 33, 539, 282, 157, NULL, 1),
            (68, 34, 273, 351, 51, NULL, 1),
            (69, 34, 539, 351, 51, NULL, 1),
            (70, 35, 273, 488, 111, NULL, 1),
            (71, 35, 539, 489, 111, NULL, 1),
            (72, 36, 273, 156, 154, NULL, 1),
            (73, 36, 539, 156, 154, NULL, 1),
            (74, 37, 273, 336, 47, NULL, 1),
            (75, 37, 539, 337, 47, NULL, 1),
            (76, 38, 273, 0, 107, NULL, 1),
            (77, 38, 539, 0, 107, NULL, 1),
            (78, 39, 273, 0, 151, NULL, 1),
            (79, 39, 539, 0, 151, NULL, 1),
            (80, 40, 273, 226, 44, NULL, 1),
            (81, 40, 539, 226, 44, NULL, 1);
            ");
        $installer->run("
            INSERT INTO `advancedinventory_product` (`id`, `product_id`, `manage_local_stock`, `total_quantity_in_stock`) VALUES
            (1, 36, 0, 0),
            (2, 96, 0, 0),
            (3, 139, 0, 0),
            (4, 92, 0, 0),
            (5, 134, 0, 0),
            (6, 33, 0, 0),
            (7, 89, 0, 0),
            (8, 131, 0, 0),
            (9, 30, 0, 0),
            (10, 86, 0, 0),
            (11, 128, 0, 0),
            (12, 27, 0, 0),
            (13, 82, 0, 0),
            (14, 124, 0, 0),
            (15, 166, 0, 0),
            (16, 20, 0, 0),
            (17, 79, 0, 0),
            (18, 118, 0, 0),
            (19, 160, 0, 0),
            (20, 17, 0, 0),
            (21, 28, 1, 31),
            (22, 87, 1, 622),
            (23, 129, 1, 45),
            (24, 25, 1, -14),
            (25, 84, 1, 859),
            (26, 125, 1, 422),
            (27, 18, 1, 8),
            (28, 80, 1, 8),
            (29, 121, 1, 812),
            (30, 161, 1, 208),
            (31, 74, 1, 0),
            (32, 115, 1, 344),
            (33, 157, 1, 563),
            (34, 51, 1, 702),
            (35, 111, 1, 977),
            (36, 154, 1, 312),
            (37, 47, 1, 673),
            (38, 107, 1, 0),
            (39, 151, 1, 0),
            (40, 44, 1, 452);
            ");
    }
    $installer->endSetup();
}