<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Rma
 */
$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$this->startSetup();

$this->run("
    ALTER TABLE `{$this->getTable('amrma/request')}`
    ADD COLUMN `field_1` VARCHAR(255) DEFAULT NULL,
    ADD COLUMN `field_2` VARCHAR(255) DEFAULT NULL,
    ADD COLUMN `field_3` VARCHAR(255) DEFAULT NULL,
    ADD COLUMN `field_4` VARCHAR(255) DEFAULT NULL,
    ADD COLUMN `field_5` VARCHAR(255) DEFAULT NULL;
");

$this->endSetup();