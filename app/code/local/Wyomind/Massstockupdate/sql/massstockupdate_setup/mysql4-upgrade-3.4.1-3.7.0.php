<?php

$installer = $this;

$installer->startSetup();

$installer->run("ALTER TABLE {$this->getTable('massstockupdate_import')} 
    ADD `identifier_code` text
   ");

$installer->endSetup();