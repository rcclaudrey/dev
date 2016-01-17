<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Acart
 */

$this->startSetup();

$this->run("
    alter table `{$this->getTable('amacart/canceled')}`
    change column reason `reason` ENUM('elapsed','bought','link','blacklist','admin','updated');
");

$this->endSetup(); 