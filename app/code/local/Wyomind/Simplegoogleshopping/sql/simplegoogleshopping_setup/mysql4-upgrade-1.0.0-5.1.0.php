<?php

$installer = $this;

$installer->startSetup();

$installer->run("ALTER TABLE {$this->getTable('simplegoogleshopping')} ADD `simplegoogleshopping_categories`  VARCHAR(200); ");

$installer->endSetup();