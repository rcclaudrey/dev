<?php

$installer = $this;

$installer->startSetup();

$installer->run("ALTER TABLE {$this->getTable('pointofsale')} CHANGE `order` position INT(4) NOT NULL DEFAULT '1'; ");

$installer->endSetup();