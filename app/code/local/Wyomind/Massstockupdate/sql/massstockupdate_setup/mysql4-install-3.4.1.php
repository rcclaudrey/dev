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
   
  
  PRIMARY KEY (`profile_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

if (strpos($_SERVER['HTTP_HOST'], "wyomind.com")!=-1) {
    $installer->run(
            "INSERT INTO `{$this->getTable('massstockupdate_import')}` (`profile_id`,`profile_name`,`file_path`,`file_separator`,`file_enclosure`,`auto_set_total`,`auto_set_instock`,`mapping`,`cron_setting`,`imported_at`,`sku_offset`,`use_custom_rules`,`custom_rules`) VALUES
            (1,'backup_restore','/var/backups/stockupdater-demofile.csv',';','none',0,1,'{\"columns\":[{\"label\":\"France Paris Store\",\"value\":\"541\",\"id\":\"541\"},{\"label\":\"Spain Barcelona Store\",\"value\":\"542\",\"id\":\"542\"},{\"label\":\"United Kingdom London Store\",\"value\":\"543\",\"id\":\"543\"},{\"label\":\"Manage Local Stock\",\"value\":\"manage_local_stock\",\"id\":\"manage_local_stock\"}]}','{\"days\":[\"Monday\",\"Tuesday\",\"Wednesday\",\"Thursday\",\"Friday\",\"Saturday\",\"Sunday\"],\"hours\":[\"00:00\",\"01:00\",\"02:00\",\"03:00\",\"04:00\",\"05:00\",\"06:00\",\"07:00\",\"08:00\",\"09:00\",\"10:00\",\"11:00\",\"00:30\",\"01:30\",\"02:30\",\"03:30\",\"04:30\",\"05:30\",\"06:30\",\"07:30\",\"08:30\",\"09:30\",\"10:30\",\"11:30\",\"12:00\",\"13:00\",\"14:00\",\"15:00\",\"16:00\",\"17:00\",\"18:00\",\"19:00\",\"20:00\",\"21:00\",\"22:00\",\"23:00\",\"12:30\",\"13:30\",\"14:30\",\"15:30\",\"16:30\",\"17:30\",\"18:30\",\"19:30\",\"20:30\",\"21:30\",\"22:30\",\"23:30\"]}','2014-05-28 13:02:36',1,0,null);"
    );
}

        
 
$installer->endSetup();
