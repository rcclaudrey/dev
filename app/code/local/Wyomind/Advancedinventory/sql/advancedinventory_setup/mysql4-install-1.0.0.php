<?php

if (Mage::getConfig()->getModuleConfig("Wyomind_Pointofsale")->is("active", "true")) {


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


// ajout du champ delivery_rules dans Pointofsale

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



    $installer->endSetup();
}