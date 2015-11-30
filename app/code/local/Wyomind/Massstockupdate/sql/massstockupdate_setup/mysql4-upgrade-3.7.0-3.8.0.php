<?php

$installer = $this;

$installer->startSetup();

$installer->run("ALTER TABLE {$this->getTable('massstockupdate_import')} ADD `file_system_type` int(1) DEFAULT 0");
$installer->run("ALTER TABLE {$this->getTable('massstockupdate_import')} ADD `use_sftp` INT(1) DEFAULT 0");
$installer->run("ALTER TABLE {$this->getTable('massstockupdate_import')} ADD `ftp_host` VARCHAR(300) DEFAULT NULL");
$installer->run("ALTER TABLE {$this->getTable('massstockupdate_import')} ADD `ftp_login` VARCHAR(300) DEFAULT NULL");
$installer->run("ALTER TABLE {$this->getTable('massstockupdate_import')} ADD `ftp_password` VARCHAR(300) DEFAULT NULL");
$installer->run("ALTER TABLE {$this->getTable('massstockupdate_import')} ADD `ftp_active` INT(1) DEFAULT 0");
$installer->run("ALTER TABLE {$this->getTable('massstockupdate_import')} ADD `ftp_dir` VARCHAR(300) DEFAULT NULL");


$installer->endSetup();