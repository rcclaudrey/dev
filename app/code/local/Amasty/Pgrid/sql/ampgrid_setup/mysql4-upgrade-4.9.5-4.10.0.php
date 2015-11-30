<?php
 /**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Pgrid
 */

/**
 * @var Magento_Db_Adapter_Pdo_Mysql $this
 */
$this->startSetup();

$this->run(
    "CREATE TABLE `{$this->getTable('ampgrid/grid_group')}`(
      `entity_id` INT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
      `user_id` INT(8) UNSIGNED NOT NULL,
      `title` VARCHAR(255) NOT NULL,
      `attributes` VARCHAR(255) DEFAULT '',
      `additional_columns` VARCHAR(255) DEFAULT '',
      `is_default` TINYINT(5) DEFAULT 0,
      PRIMARY KEY  (`entity_id`),
      KEY `IND_AM_PGRID_ORDER_USER_ID` (`user_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;"
);

$this->endSetup();