<?php

$installer = $this;
$installer->startSetup();
$installer->run("
DROP TABLE IF   EXISTS {$this->getTable('watchlog')};
 ");

$sql="CREATE TABLE IF NOT EXISTS `{$this->getTable('watchlog')}` (
  `watchlog_id` int(11) NOT NULL AUTO_INCREMENT,
  `ip` varchar(15),
  `date` datetime NOT NULL,
  `login` varchar(200),
  `useragent` varchar(1000),
  `message` varchar(200),
  `type` char(1),
  `url` varchar(500),
  PRIMARY KEY (`watchlog_id`)
)";

$installer->run($sql);

$installer->endSetup();
	 
