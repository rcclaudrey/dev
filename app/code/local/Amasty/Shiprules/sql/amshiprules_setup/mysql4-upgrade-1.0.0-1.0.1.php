<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Shiprules
 */
$this->startSetup();

$this->run("
CREATE TABLE `{$this->getTable('amshiprules/attribute')}` (
  `attr_id` mediumint(8) unsigned NOT NULL auto_increment,
  `rule_id` mediumint(8) unsigned NOT NULL,
  `code`    varchar(255) NOT NULL default '',
  PRIMARY KEY  (`attr_id`),
  CONSTRAINT `FK_SHIPRULES_RULE` FOREIGN KEY (`rule_id`) REFERENCES {$this->getTable('amshiprules/rule')} (`rule_id`) ON DELETE CASCADE ON UPDATE CASCADE 
) ENGINE=InnoDB DEFAULT CHARSET=utf8; 
"); 

$this->endSetup();