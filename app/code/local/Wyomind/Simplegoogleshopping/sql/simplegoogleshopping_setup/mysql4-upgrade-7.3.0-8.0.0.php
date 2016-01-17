<?php

$installer = $this;

$installer->startSetup();
$installer->run("ALTER TABLE {$this->getTable('simplegoogleshopping')} 
 ADD   `simplegoogleshopping_attribute_sets` varchar(150) default '*';
");


$installer->endSetup();