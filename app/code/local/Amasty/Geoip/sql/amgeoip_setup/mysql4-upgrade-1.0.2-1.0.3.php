<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Geoip
 */


$this->startSetup();

$this->run("
    TRUNCATE TABLE `{$this->getTable('amgeoip/location')}`;
    TRUNCATE TABLE `{$this->getTable('amgeoip/block')}`;

    alter table `{$this->getTable('amgeoip/location')}`
    drop column `region`,
    drop column `postal_code`,
    drop column `latitude`,
    drop column `longitude`,
    drop column `dma_code`,
    drop column `area_code`;

    alter table `{$this->getTable('amgeoip/block')}`
    add column `postal_code` CHAR(5) NULL DEFAULT NULL;

    alter table `{$this->getTable('amgeoip/block')}`
    add column `latitude` FLOAT NULL DEFAULT NULL;

    alter table `{$this->getTable('amgeoip/block')}`
    add column `longitude` FLOAT NULL DEFAULT NULL;

");

Mage::getConfig()->saveConfig('amgeoip/import/block', 0);
Mage::getConfig()->saveConfig('amgeoip/import/location', 0);

$title = 'Amasty`s extension Geo Ip Data has been installed. Please import Geo Ip Data.';
$description = 'You can see versions of the installed extensions right in the admin, as well as configure notifications about major updates.';

Mage::getModel('adminnotification/inbox')->add(Mage_AdminNotification_Model_Inbox::SEVERITY_NOTICE, $title, $description);

$this->endSetup();
