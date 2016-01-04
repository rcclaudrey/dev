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
  `use_custom_rules` int(1) DEFAULT 0,
  `custom_rules` text,
  `identifier_code` text,
  PRIMARY KEY (`profile_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

if (strpos($_SERVER['HTTP_HOST'], "wyomind.com")!==FALSE) {
    $installer->run(
            "INSERT INTO `massstockupdate_import` (`profile_id`,`auto_set_total`,`auto_set_instock`, `profile_name`, `file_path`, `file_separator`, `file_enclosure`, `mapping`, `cron_setting`, `imported_at`,sku_offset) VALUES
            (1,0,1, 'backup_restore', '/var/backups/stockupdater-demofile.csv', ';', 'none', '{\"columns\":[{\"label\":\"France Paris Store\",\"value\":\"541\",\"id\":\"541\"},{\"label\":\"Spain Barcelona Store\",\"value\":\"542\",\"id\":\"542\"},{\"label\":\"United Kingdom London Store\",\"value\":\"543\",\"id\":\"543\"},{\"label\":\"Germany Berlin Store\",\"value\":\"544\",\"id\":\"544\"},{\"label\":\"Belgium Brussels Store\",\"value\":\"545\",\"id\":\"545\"},{\"label\":\"Italy Rome Store\",\"value\":\"546\",\"id\":\"546\"},{\"label\":\"USA Miami Store\",\"value\":\"548\",\"id\":\"548\"},{\"label\":\"USA Los Angeles Store\",\"value\":\"549\",\"id\":\"549\"},{\"label\":\"USA San Francisco Store\",\"value\":\"550\",\"id\":\"550\"},{\"label\":\"USA Chicago Store\",\"value\":\"551\",\"id\":\"551\"},{\"label\":\"USA Washington Store\",\"value\":\"552\",\"id\":\"552\"},{\"label\":\"USA Detroit Store\",\"value\":\"553\",\"id\":\"553\"},{\"label\":\"USA Louisville Store\",\"value\":\"555\",\"id\":\"555\"},{\"label\":\"USA Houston Store\",\"value\":\"556\",\"id\":\"556\"},{\"label\":\"USA Oklahoma City Store\",\"value\":\"557\",\"id\":\"557\"},{\"label\":\"USA Dallas Store\",\"value\":\"558\",\"id\":\"558\"},{\"label\":\"USA Salt Lakie City Store\",\"value\":\"559\",\"id\":\"559\"},{\"label\":\"Mexico Mexico Store\",\"value\":\"560\",\"id\":\"560\"},{\"label\":\"Canada Vancouver Store\",\"value\":\"561\",\"id\":\"561\"},{\"label\":\"Canada Ottawa Store\",\"value\":\"562\",\"id\":\"562\"},{\"label\":\"South Africa Johannesburg Store\",\"value\":\"563\",\"id\":\"563\"},{\"label\":\"China Beijing Store\",\"value\":\"566\",\"id\":\"566\"},{\"label\":\"Australia Sydney Store\",\"value\":\"567\",\"id\":\"567\"},{\"label\":\"Turkey Istanbul Store\",\"value\":\"568\",\"id\":\"568\"},{\"label\":\"South Korea Seoul Store\",\"value\":\"570\",\"id\":\"570\"},{\"label\":\"Malaysia Kuala Lumpur Store\",\"value\":\"571\",\"id\":\"571\"},{\"label\":\"Hungary Budapest Store\",\"value\":\"573\",\"id\":\"573\"},{\"label\":\"Total Stock\",\"value\":\"total\",\"id\":\"total\"},{\"label\":\"not used\",\"value\":\"not-used\",\"id\":\"not-used\"}]}', '{\"days\":[\"Monday\",\"Tuesday\",\"Wednesday\",\"Thursday\",\"Friday\",\"Saturday\",\"Sunday\"],\"hours\":[\"00:00\",\"01:00\",\"02:00\",\"03:00\",\"04:00\",\"05:00\",\"06:00\",\"07:00\",\"08:00\",\"09:00\",\"10:00\",\"11:00\",\"00:30\",\"01:30\",\"02:30\",\"03:30\",\"04:30\",\"05:30\",\"06:30\",\"07:30\",\"08:30\",\"09:30\",\"10:30\",\"11:30\",\"12:00\",\"13:00\",\"14:00\",\"15:00\",\"16:00\",\"17:00\",\"18:00\",\"19:00\",\"20:00\",\"21:00\",\"22:00\",\"23:00\",\"12:30\",\"13:30\",\"14:30\",\"15:30\",\"16:30\",\"17:30\",\"18:30\",\"19:30\",\"20:30\",\"21:30\",\"22:30\",\"23:30\"]}', '2013-02-27 21:01:34',1);"
    );
}

$installer->endSetup();
