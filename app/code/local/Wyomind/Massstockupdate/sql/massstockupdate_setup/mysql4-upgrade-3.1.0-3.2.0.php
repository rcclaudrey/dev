<?php

$installer = $this;

$installer->startSetup();

$installer->run("ALTER TABLE {$this->getTable('massstockupdate_import')} 
    ADD `use_custom_rules` int(1) DEFAULT 0,
    ADD `custom_rules` text;
   ");

$installer->endSetup();