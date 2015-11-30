<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Shiprules
 */
$this->startSetup();

$this->run("
 ALTER TABLE `{$this->getTable('amshiprules/rule')}` ADD `days` varchar(255) NOT NULL default '' AFTER `name`;   
 ALTER TABLE `{$this->getTable('amshiprules/rule')}` ADD `discount_id` int NOT NULL default 0 AFTER `calc`;   
");

  
$this->endSetup();