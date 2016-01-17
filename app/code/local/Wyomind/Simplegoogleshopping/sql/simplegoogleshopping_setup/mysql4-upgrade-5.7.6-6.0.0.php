<?php

$installer = $this;

$installer->startSetup();

$installer->run("ALTER TABLE {$this->getTable('simplegoogleshopping')} 
MODIFY `cron_expr` varchar(900) NOT NULL DEFAULT '{\"days\":[\"Monday\",\"Tuesday\",\"Wednesday\",\"Thursday\",\"Friday\",\"Saturday\",\"Sunday\"],\"hours\":[\"00:00\",\"04:00\",\"08:00\",\"12:00\",\"16:00\",\"20:00\"]}',
MODIFY `simplegoogleshopping_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP; 
;");

$installer->endSetup();