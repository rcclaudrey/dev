<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Acart
 */

$this->startSetup();

$this->run("
    alter table `{$this->getTable('amacart/rule')}`
    add column `utm_source`  VARCHAR(255) NOT NULL DEFAULT '';

    alter table `{$this->getTable('amacart/rule')}`
    add column `utm_medium`  VARCHAR(255) NOT NULL DEFAULT '';

    alter table `{$this->getTable('amacart/rule')}`
    add column `utm_term`  VARCHAR(255) NOT NULL DEFAULT '';

    alter table `{$this->getTable('amacart/rule')}`
    add column `utm_content`  VARCHAR(255) NOT NULL DEFAULT '';

    alter table `{$this->getTable('amacart/rule')}`
    add column `utm_campaign`  VARCHAR(255) NOT NULL DEFAULT '';
");

$this->endSetup();