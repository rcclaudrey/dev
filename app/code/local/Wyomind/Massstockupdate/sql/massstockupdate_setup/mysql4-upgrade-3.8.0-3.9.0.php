<?php

$installer = $this;

$installer->startSetup();

$installer->run("ALTER TABLE {$this->getTable('massstockupdate_import')} ADD `file_type` int(1) DEFAULT 0");
$installer->run("ALTER TABLE {$this->getTable('massstockupdate_import')} ADD `xpath_to_product` varchar(400) ");


$installer->endSetup();