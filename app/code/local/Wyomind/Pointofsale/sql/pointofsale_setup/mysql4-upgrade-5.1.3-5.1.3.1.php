<?php

$this->startSetup();

$this->run("ALTER TABLE {$this->getTable('pointofsale')} ADD `ship_time` INT(1)  UNSIGNED NOT NULL DEFAULT '0'");

$this->endSetup();