<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */
$this->startSetup();

/**
 * @Migration field_exist:amshopby/filter|exclude_from:1
 */
$this->run("
    ALTER TABLE `{$this->getTable('amshopby/filter')}` ADD `exclude_from` VARCHAR(255) NOT NULL;
"); 

$this->endSetup();