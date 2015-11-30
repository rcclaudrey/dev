<?php

$installer = $this;
$installer->startSetup();
$installer->run("
 DROP TABLE IF EXISTS {$this->getTable('massstockupdate_import')};
 CREATE TABLE {$this->getTable('massstockupdate_import')} (
  `profile_id` int(11) NOT NULL AUTO_INCREMENT,
  `profile_name` varchar(100) DEFAULT NULL,
  `file_path` varchar(250) DEFAULT NULL,
  `file_separator` varchar(4) DEFAULT ';',
  `file_enclosure` varchar(4) DEFAULT ';',
  `auto_set_total` int(1) DEFAULT 1,
  `auto_set_instock` int(1) DEFAULT 1,
  `mapping` text,
  `cron_setting` text ,
  `imported_at` datetime DEFAULT NULL,
  `sku_offset`  INT(2) DEFAULT 0,
  PRIMARY KEY (`profile_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

if (strpos($_SERVER['HTTP_HOST'], "wyomind.com")) {
    $installer->run(
            "INSERT INTO `massstockupdate_import` (`profile_id`,`auto_set_total`,`auto_set_instock`, `profile_name`, `file_path`, `file_separator`, `file_enclosure`, `mapping`, `cron_setting`, `imported_at`) VALUES
            (1,0,1, 'backup_restore', '/var/backups/stockupdater-1361970033.csv', ';', 'none', '{\"columns\":[{\"label\":\"Wyomind\",\"value\":\"273\",\"id\":\"273\"},{\"label\":\"Wyomind NY\",\"value\":\"539\",\"id\":\"539\"},{\"label\":\"Total Stock\",\"value\":\"total\",\"id\":\"total\"}]}', '{\"days\":[\"Monday\",\"Tuesday\",\"Wednesday\",\"Thursday\",\"Friday\",\"Saturday\",\"Sunday\"],\"hours\":[\"00:00\",\"01:00\",\"02:00\",\"03:00\",\"04:00\",\"05:00\",\"06:00\",\"07:00\",\"08:00\",\"09:00\",\"10:00\",\"11:00\",\"00:30\",\"01:30\",\"02:30\",\"03:30\",\"04:30\",\"05:30\",\"06:30\",\"07:30\",\"08:30\",\"09:30\",\"10:30\",\"11:30\",\"12:00\",\"13:00\",\"14:00\",\"15:00\",\"16:00\",\"17:00\",\"18:00\",\"19:00\",\"20:00\",\"21:00\",\"22:00\",\"23:00\",\"12:30\",\"13:30\",\"14:30\",\"15:30\",\"16:30\",\"17:30\",\"18:30\",\"19:30\",\"20:30\",\"21:30\",\"22:30\",\"23:30\"]}', '2013-02-27 21:01:34');"
    );
}

$installer->endSetup();