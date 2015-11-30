<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Rma
 */ 

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$this->startSetup();

$this->run("

CREATE TABLE `{$this->getTable('amrma/request')}` (
  `request_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` SMALLINT(5) UNSIGNED DEFAULT NULL,
  `order_id` INT(10) UNSIGNED DEFAULT NULL,
  `increment_id` VARCHAR(50) DEFAULT NULL,
  `email` VARCHAR(255) DEFAULT NULL,
  `customer_id` INT(10) UNSIGNED DEFAULT NULL,
  `customer_firstname` VARCHAR(255) DEFAULT NULL,
  `customer_lastname` VARCHAR(255) DEFAULT NULL,
  `status_id` INT(10) UNSIGNED DEFAULT NULL,
  `code` VARCHAR(255) DEFAULT NULL,
  `notes` TEXT,
  `created` DATETIME NOT NULL,
  `updated`  DATETIME NOT NULL,
  `allow_create_label` TINYINT(1) NOT NULL DEFAULT '0',
  `is_shipped` TINYINT(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`request_id`),
  KEY `FK_amasty_amrma_request_status` (`status_id`),
  KEY `increment_id` (`increment_id`),
  KEY `FK_amasty_amrma_request_order` (`order_id`),
  KEY `FK_amasty_amrma_request_customer` (`customer_id`),
  KEY `FK_am_arma_request_core_store` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `{$this->getTable('amrma/comment')}` (
  `comment_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `request_id` INT(10) UNSIGNED NOT NULL,
  `comment_value` TEXT,
  `is_admin` TINYINT(1) DEFAULT NULL,
  `created` DATETIME NOT NULL,
  `unique_key` CHAR(128) NOT NULL,
  PRIMARY KEY (`comment_id`),
  UNIQUE KEY `unique_key` (`unique_key`),
  KEY `FK_amasty_amrma_comment_request` (`request_id`),
  CONSTRAINT `FK_amasty_amrma_comment_request` FOREIGN KEY (`request_id`) REFERENCES `{$this->getTable('amrma/request')}` (`request_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `{$this->getTable('amrma/file')}` (
  `file_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `comment_id` int(10) unsigned NOT NULL,
  `file` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`file_id`),
  KEY `FK_amasty_amrma_comment_file_comment` (`comment_id`),
  CONSTRAINT `FK_amasty_amrma_comment_file_comment` FOREIGN KEY (`comment_id`) REFERENCES `{$this->getTable('amrma/comment')}` (`comment_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `{$this->getTable('amrma/item')}` (
  `item_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `request_id` INT(10) UNSIGNED DEFAULT NULL,
  `sales_item_id` INT(10) UNSIGNED DEFAULT NULL,
  `product_id` INT(10) UNSIGNED DEFAULT NULL,
  `sku` VARCHAR(64) DEFAULT NULL,
  `name` VARCHAR(255) DEFAULT NULL,
  `qty` INT(10) UNSIGNED DEFAULT '0',
  `reason` VARCHAR(255) DEFAULT NULL,
  `condition` VARCHAR(255) DEFAULT NULL,
  `resolution` VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (`item_id`),
  KEY `FK_amasty_amrma_item_product` (`product_id`),
  KEY `FK_amasty_amrma_item_sales_flat_order_item` (`sales_item_id`),
  KEY `FK_amasty_amrma_item_request` (`request_id`),
  CONSTRAINT `FK_amasty_amrma_item_request` FOREIGN KEY (`request_id`) REFERENCES `{$this->getTable('amrma/request')}` (`request_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `{$this->getTable('amrma/status')}` (
  `status_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `is_active` tinyint(1) NOT NULL DEFAULT '0',
  `order_number` int(10) unsigned DEFAULT NULL,
  `status_key` varchar(256) DEFAULT NULL,
  PRIMARY KEY (`status_id`),
  KEY `status_key` (`status_key`(255))
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `{$this->getTable('amrma/label')}` (
  `label_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Label Id',
  `status_id` int(10) unsigned NOT NULL COMMENT 'Status Id',
  `store_id` smallint(5) unsigned NOT NULL COMMENT 'Store Id',
  `label` varchar(255) DEFAULT NULL COMMENT 'Label',
  PRIMARY KEY (`label_id`),
  UNIQUE KEY `UNQ_AMRMA_STATUS_LABEL_STATUS_ID_STORE_ID` (`status_id`,`store_id`),
  KEY `IDX_AMRMA_STATUS_LABEL_STORE_ID` (`store_id`),
  KEY `IDX_AMRMA_STATUS_LABEL_STATUS_ID` (`status_id`),
  CONSTRAINT `FK_AMRMA_STATUS_LABEL_STATUS_ID_AMRMA_STATUS_STATUS_ID` FOREIGN KEY (`status_id`) REFERENCES `{$this->getTable('amrma/status')}` (`status_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `{$this->getTable('amrma/template')}` (
  `template_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'template Id',
  `status_id` int(10) unsigned NOT NULL COMMENT 'Status Id',
  `store_id` smallint(5) unsigned NOT NULL COMMENT 'Store Id',
  `template` INT(10) UNSIGNED COMMENT 'template',
  PRIMARY KEY (`template_id`),
  UNIQUE KEY `UNQ_AMRMA_STATUS_template_STATUS_ID_STORE_ID` (`status_id`,`store_id`),
  KEY `IDX_AMRMA_STATUS_template_STORE_ID` (`store_id`),
  KEY `IDX_AMRMA_STATUS_template_STATUS_ID` (`status_id`),
  CONSTRAINT `FK_AMRMA_STATUS_template_STATUS_ID_AMRMA_STATUS_STATUS_ID` FOREIGN KEY (`status_id`) REFERENCES `{$this->getTable('amrma/status')}` (`status_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `{$this->getTable('amrma/status')}` (`is_active`, `order_number`, `status_key`) VALUES
(1, 1, 'pending');
INSERT INTO `{$this->getTable('amrma/label')}` (status_id, store_id, label) VALUES
(LAST_INSERT_ID(), 0, 'NEW');

INSERT INTO `{$this->getTable('amrma/status')}` (`is_active`, `order_number`, `status_key`) VALUES
(1, 2, NULL);
INSERT INTO `{$this->getTable('amrma/label')}` (status_id, store_id, label) VALUES
(LAST_INSERT_ID(), 0, 'Processing');

INSERT INTO `{$this->getTable('amrma/status')}` (`is_active`, `order_number`, `status_key`) VALUES
(1, 3, NULL);
INSERT INTO `{$this->getTable('amrma/label')}` (status_id, store_id, label) VALUES
(LAST_INSERT_ID(), 0, 'Product Shipped');

INSERT INTO `{$this->getTable('amrma/status')}` (`is_active`, `order_number`, `status_key`) VALUES
(1, 4, NULL);
INSERT INTO `{$this->getTable('amrma/label')}` (status_id, store_id, label) VALUES
(LAST_INSERT_ID(), 0, 'Product Received');

INSERT INTO `{$this->getTable('amrma/status')}` (`is_active`, `order_number`, `status_key`) VALUES
(1, 5, NULL);
INSERT INTO `{$this->getTable('amrma/label')}` (status_id, store_id, label) VALUES
(LAST_INSERT_ID(), 0, 'Completed');

");

$this->endSetup(); 