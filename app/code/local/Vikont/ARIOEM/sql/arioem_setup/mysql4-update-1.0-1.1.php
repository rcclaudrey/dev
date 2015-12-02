<?php

$this->startSetup();
$this->run('ALTER TABLE `{$this->getTable("customer/group")}` ADD `cost_percent` FLOAT NULL DEFAULT NULL');
$this->endSetup();