<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Xsearch
 */

$this->startSetup();

$this->run("
    CREATE TABLE `{$this->getTable('amxsearch/fulltext')}` (
      `fulltext_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Entity ID',
      `product_id` INT(10) UNSIGNED NOT NULL COMMENT 'Product ID',
      `store_id` SMALLINT(5) UNSIGNED NOT NULL COMMENT 'Store ID',
      `data_index` LONGTEXT COMMENT 'Data index',
      `col_1` TEXT,
      `col_2` TEXT,
      `col_3` TEXT,
      `col_4` TEXT,
      `col_5` TEXT,
      `col_6` TEXT,
      `col_7` TEXT,
      `col_8` TEXT,
      `col_9` TEXT,
      `col_10` TEXT,
      PRIMARY KEY (`fulltext_id`),
      UNIQUE KEY `UNQ_AMXSEARCH_FULLTEXT_PRODUCT_ID_STORE_ID` (`product_id`,`store_id`),
      FULLTEXT KEY `FTI_AMXSEARCH_FULLTEXT_DATA_INDEX` (`data_index`),
      FULLTEXT KEY `AM_FULLTEXT_col_1` (`col_1`),
      FULLTEXT KEY `AM_FULLTEXT_col_2` (`col_2`),
      FULLTEXT KEY `AM_FULLTEXT_col_3` (`col_3`),
      FULLTEXT KEY `AM_FULLTEXT_col_4` (`col_4`),
      FULLTEXT KEY `AM_FULLTEXT_col_5` (`col_5`),
      FULLTEXT KEY `AM_FULLTEXT_col_6` (`col_6`),
      FULLTEXT KEY `AM_FULLTEXT_col_7` (`col_7`),
      FULLTEXT KEY `AM_FULLTEXT_col_8` (`col_8`),
      FULLTEXT KEY `AM_FULLTEXT_col_9` (`col_9`),
      FULLTEXT KEY `AM_FULLTEXT_col_10` (`col_10`)
    ) ENGINE=MYISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='Amast Xsearch result table';
");

$this->endSetup();