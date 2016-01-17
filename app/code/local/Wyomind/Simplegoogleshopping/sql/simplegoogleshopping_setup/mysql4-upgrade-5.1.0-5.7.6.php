<?php

$installer = $this;

$installer->startSetup();

$installer->run("ALTER TABLE {$this->getTable('simplegoogleshopping')} 
ADD `cron_expr` VARCHAR(200) NOT NULL DEFAULT '* * * * *',
MODIFY `simplegoogleshopping_categories` LONGTEXT; ");

$installer->endSetup();
