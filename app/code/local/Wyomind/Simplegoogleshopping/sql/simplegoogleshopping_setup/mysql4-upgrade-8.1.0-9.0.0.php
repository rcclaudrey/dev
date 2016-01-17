<?php

$installer = $this;

$installer->startSetup();
$installer->run("ALTER TABLE {$this->getTable('simplegoogleshopping')} 
 ADD   `simplegoogleshopping_category_type` int(1) default '0',
 ADD   `simplegoogleshopping_report` text ;
");


$installer->endSetup();