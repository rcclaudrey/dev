<?php

$installer = $this;

$installer->startSetup();

$installer->run("ALTER TABLE {$this->getTable('massstockupdate_import')} ADD `sku_offset`  INT(2) DEFAULT 0; ");

$installer->endSetup();