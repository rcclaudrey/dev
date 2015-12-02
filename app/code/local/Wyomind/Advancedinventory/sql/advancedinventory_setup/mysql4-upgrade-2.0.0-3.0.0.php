<?php

$installer = $this;
$installer->startSetup();
$installer->run(" DROP TABLE IF EXISTS {$this->getTable('advancedinventory_import')};");

$installer->endSetup();