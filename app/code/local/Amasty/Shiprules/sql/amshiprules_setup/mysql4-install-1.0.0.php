<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Shiprules
 */
$this->startSetup();
$this->run("
CREATE TABLE `{$this->getTable('amshiprules/rule')}` (
  `rule_id`     mediumint(8) unsigned NOT NULL auto_increment,
  `is_active`   tinyint(1) unsigned NOT NULL default '0',

  `calc`         tinyint(1) unsigned NOT NULL default '0',
  `ignore_promo` tinyint(1) unsigned NOT NULL default '0',

  `pos`         mediumint  unsigned NOT NULL default '0',

  `price_from`  decimal(12,2) unsigned NOT NULL default '0',
  `price_to`    decimal(12,2) unsigned NOT NULL default '0',

  `weight_from` decimal(12,4) unsigned NOT NULL default '0',
  `weight_to`   decimal(12,4) unsigned NOT NULL default '0',

  `qty_from`    int unsigned NOT NULL default '0',
  `qty_to`      int unsigned NOT NULL default '0',

  `rate_base`        decimal(12,2) unsigned NOT NULL default '0',
  `rate_fixed`       decimal(12,2) unsigned NOT NULL default '0',
  `rate_percent`     double        unsigned NOT NULL default '0',
  `rate_min`         decimal(12,2) unsigned NOT NULL default '0',
  `rate_max`         decimal(12,2)  unsigned NOT NULL default '0',

  `name`        varchar(255) default '', 

  `stores`      varchar(255) NOT NULL default '', 
  `cust_groups` varchar(255) NOT NULL default '', 

  `carriers`    text, 
  `methods`     text, 

  `conditions_serialized`  text, 
  `actions_serialized`     text, 
  
  PRIMARY KEY  (`rule_id`)  
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");
$this->endSetup();