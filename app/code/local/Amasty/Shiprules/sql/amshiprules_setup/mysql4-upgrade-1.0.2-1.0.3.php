<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Shiprules
 */
$this->startSetup();

$this->run("
 ALTER TABLE `{$this->getTable('amshiprules/rule')}` ADD `coupon` varchar(255) AFTER `methods`;  
"); 

$this->endSetup();