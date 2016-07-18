<?php

$this->startSetup();
$this->run("
	CREATE TABLE `oem_cost` (
		`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
		`part_number` varchar(50) NOT NULL DEFAULT '',
		`supplier_code` varchar(5) NOT NULL DEFAULT '',
		`part_name` varchar(50) NOT NULL DEFAULT '',
		`available` tinyint(1) unsigned NOT NULL DEFAULT '1',
		`cost` decimal(12,2) NOT NULL DEFAULT '0.00',
		`msrp` decimal(12,2) NOT NULL DEFAULT '0.00',
		`price` decimal(12,2) NOT NULL DEFAULT '0.00',
		`hide_price` tinyint(1) unsigned NOT NULL DEFAULT '0',
		`inv_local` int(10) DEFAULT '0',
		`inv_wh` int(10) DEFAULT '0',
		`dim_length` decimal(10,2) NOT NULL DEFAULT '0.00',
		`dim_width` decimal(10,2) NOT NULL DEFAULT '0.00',
		`dim_height` decimal(10,2) NOT NULL DEFAULT '0.00',
		`oversized` tinyint(1) unsigned NOT NULL DEFAULT '0',
		`weight` decimal(10,2) NOT NULL DEFAULT '0.00',
		`uom` varchar(3) NOT NULL DEFAULT '',
		`image_url` varchar(90) NOT NULL DEFAULT '',
		PRIMARY KEY (`id`),
		UNIQUE KEY `BRAND_PARTNO` (`supplier_code`,`part_number`),
		KEY `supplier_code` (`supplier_code`),
		KEY `part_number` (`part_number`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='OEM Parts'
");
$this->endSetup();