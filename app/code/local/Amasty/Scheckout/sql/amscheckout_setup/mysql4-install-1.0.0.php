<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Scheckout
 */

/*
 * DROP TABLE am_scheckout_field_store;
DROP TABLE am_scheckout_area_store;
DROP TABLE am_scheckout_field;
DROP TABLE am_scheckout_area;
DROP TABLE `am_scheckout_config`;

DROP TABLE `am_scheckout_block`;
DROP TABLE `am_scheckout_country`;
DROP TABLE `am_scheckout_location`;
 
DELETE FROM core_resource WHERE `code` = 'amscheckout_setup';
 */
$this->startSetup();

$this->run("
    
CREATE TABLE `{$this->getTable('amscheckout/config')}` (
  `config_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` SMALLINT(5) UNSIGNED DEFAULT NULL,
  `variable` VARCHAR(255) DEFAULT NULL,
  `value` VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (`config_id`),
  KEY `IDX_AM_SCHECKOUT_CONFIG_STORE_ID` (`store_id`),
  UNIQUE KEY `store_id_variable` (`store_id`, `variable`),
  CONSTRAINT `FK_AM_SCHECKOUT_CONFIG_STORE_ID_CORE_STORE_STORE_ID` FOREIGN KEY (`store_id`) REFERENCES `{$this->getTable('core/store')}` (`store_id`) ON DELETE CASCADE
) ENGINE=INNODB DEFAULT CHARSET=utf8;

CREATE TABLE `{$this->getTable('amscheckout/area')}` (
  `area_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `area_key` VARCHAR(60) NOT NULL,
  `default_area_label` VARCHAR(255) DEFAULT NULL,
  `area_label` VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (`area_id`),
  UNIQUE KEY `area_key` (`area_key`)
) ENGINE=INNODB DEFAULT CHARSET=utf8;

CREATE TABLE `{$this->getTable('amscheckout/area_store')}` (
  `area_store_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `area_id` INT(10) UNSIGNED DEFAULT NULL,
  `store_id` SMALLINT(5) UNSIGNED DEFAULT NULL,
  `area_label` VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (`area_store_id`),
  KEY `IDX_AM_SCHECKOUT_AREA_STORE_AREA_ID` (`area_id`),
  KEY `IDX_AM_SCHECKOUT_AREA_STORE_STORE_ID` (`store_id`),
  CONSTRAINT `FK_AM_SCHECKOUT_AREA_STORE_AREA_ID_AM_CHECKOUT_AREA_AREA_ID` FOREIGN KEY (`area_id`) REFERENCES `{$this->getTable('amscheckout/area')}` (`area_id`) ON DELETE CASCADE,
  CONSTRAINT `FK_AM_SCHECKOUT_AREA_STORE_STORE_ID_CORE_STORE_STORE_ID` FOREIGN KEY (`store_id`) REFERENCES `{$this->getTable('core/store')}` (`store_id`) ON DELETE CASCADE
) ENGINE=INNODB DEFAULT CHARSET=utf8;

CREATE TABLE `{$this->getTable('amscheckout/field')}` (
  `field_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `field_key` VARCHAR(60) NOT NULL,
  `area_id` INT(10) UNSIGNED NOT NULL,
  `default_field_label` VARCHAR(255) DEFAULT NULL,
  `default_field_order` SMALLINT(5) UNSIGNED DEFAULT NULL,
  `default_field_required` boolean default null,
  `default_column_position` SMALLINT(5) UNSIGNED DEFAULT NULL,
  `field_label` VARCHAR(255) DEFAULT NULL,
  `field_order` SMALLINT(5) UNSIGNED DEFAULT NULL,
  `field_required` boolean default null,
  `field_disabled` boolean not null default false,
  `column_position` SMALLINT(5) UNSIGNED DEFAULT NULL,
  `is_eav_attribute` boolean default false not null,
  `is_order_attribute` boolean default false not null,
  `is_customer_attribute` boolean default false not null,
  PRIMARY KEY (`field_id`),
  UNIQUE KEY `field_key` (`field_key`),
  KEY `IDX_AM_CHECKOUT_FIELD_AREA_ID` (`area_id`),
  CONSTRAINT `FK_AM_CHECKOUT_FIELD_AREA_ID_AM_CHECKOUT_AREA_AREA_ID` FOREIGN KEY (`area_id`) REFERENCES `{$this->getTable('amscheckout/area')}` (`area_id`) ON DELETE CASCADE
) ENGINE=INNODB DEFAULT CHARSET=utf8;

CREATE TABLE `{$this->getTable('amscheckout/field_store')}` (
  `field_store_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `field_id` INT(10) UNSIGNED NOT NULL,
  `store_id` SMALLINT(5) UNSIGNED NOT NULL,
  `field_label` VARCHAR(255) DEFAULT NULL,
  `field_order` SMALLINT(5) UNSIGNED DEFAULT NULL,
  `field_required` BOOLEAN DEFAULT NULL,
  `field_disabled` boolean not null default false,  
  `column_position` SMALLINT(5) UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`field_store_id`),
  KEY `IDX_AM_CHECKOUT_FIELD_STORE_FIELD_ID` (`field_id`),
  KEY `IDX_AM_CHECKOUT_FIELD_STORE_STORE_ID` (`store_id`),
  CONSTRAINT `FK_AM_CHECKOUT_FIELD_STORE_FIELD_ID_AM_CHECKOUT_FIELD_FIELD_ID` FOREIGN KEY (`field_id`) REFERENCES `{$this->getTable('amscheckout/field')}` (`field_id`) ON DELETE CASCADE,
  CONSTRAINT `FK_AM_CHECKOUT_FIELD_STORE_STORE_ID_CORE_STORE_STORE_ID` FOREIGN KEY (`store_id`) REFERENCES `{$this->getTable('core/store')}` (`store_id`) ON DELETE CASCADE
) ENGINE=INNODB DEFAULT CHARSET=utf8;

CREATE TABLE `{$this->getTable('amscheckout/country')}` (
  `ip_country_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `ip1_temp` CHAR(16) DEFAULT NULL,
  `ip2_temp` CHAR(16) DEFAULT NULL,
  `ip_from` INT(10) UNSIGNED NOT NULL,
  `ip_to` INT(10) UNSIGNED NOT NULL,
  `code` CHAR(2) NOT NULL,
  `country` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`ip_country_id`),
  KEY `ip_from` (`ip_from`),
  UNIQUE KEY `ip_to` (`ip_to`)
) ENGINE=MYISAM DEFAULT CHARSET=utf8;

CREATE TABLE `{$this->getTable('amscheckout/block')}` (
  `block_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `start_ip_num` INT(10) UNSIGNED NOT NULL,
  `end_ip_num` INT(10) UNSIGNED NOT NULL,
  `geoip_loc_id` INT(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`block_id`),
  KEY `start_ip_num` (`start_ip_num`),
  KEY `end_ip_num` (`end_ip_num`),
  KEY `geoip_loc_id` (`geoip_loc_id`)
) ENGINE=MYISAM DEFAULT CHARSET=utf8;

CREATE TABLE `{$this->getTable('amscheckout/location')}` (
  `location_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `geoip_loc_id` INT(10) UNSIGNED NOT NULL,
  `country` CHAR(2) DEFAULT NULL,
  `region` CHAR(2) DEFAULT NULL,
  `city` VARCHAR(255) DEFAULT NULL,
  `postal_code` CHAR(5) DEFAULT NULL,
  `latitude` FLOAT DEFAULT NULL,
  `longitude` FLOAT DEFAULT NULL,
  `dma_code` INT(11) DEFAULT NULL,
  `area_code` INT(11) DEFAULT NULL,
  PRIMARY KEY (`location_id`),
  KEY `geoip_loc_id` (`geoip_loc_id`)
) ENGINE=MYISAM DEFAULT CHARSET=utf8;

INSERT INTO `{$this->getTable('amscheckout/area')}` (area_key, area_label) VALUES
('billing', 'Billing'),
('shipping', 'Shipping'),
('shipping_method', 'Shipping Method'),
('payment', 'Payment'),
('review', 'Review');

UPDATE `{$this->getTable('amscheckout/area')}` set
default_area_label = area_label;

SET @billingAreaId = (SELECT area_id FROM `{$this->getTable('amscheckout/area')}` WHERE area_key = 'billing');

INSERT INTO `{$this->getTable('amscheckout/field')}` (`field_key`, `field_label`, `area_id`, `field_order`, `field_required`, `column_position`, `is_eav_attribute`) VALUES 
('billing-address-select', 'Select a billing address from your address book or enter a new address.', @billingAreaId, 0, FALSE, 100, FALSE),
('billing:prefix', 'prefix', @billingAreaId, 100, FALSE, 20, TRUE),
('billing:firstname', 'firstname', @billingAreaId, 200, TRUE, 50, TRUE),
('billing:middlename', 'middlename', @billingAreaId, 300, FALSE, 50, TRUE),
('billing:lastname', 'lastname', @billingAreaId, 400, TRUE, 50, TRUE),
('billing:suffix', 'suffix', @billingAreaId, 500, FALSE, 20, TRUE),
('billing:company', 'Company', @billingAreaId, 600, FALSE, 50, FALSE),
('billing:email', 'Email', @billingAreaId, 700, TRUE, 50, FALSE),
('billing:street1', 'Address', @billingAreaId, 800, TRUE, 100, FALSE),
('billing:vat_id', 'VAT Number', @billingAreaId, 900, FALSE, 50, FALSE),
('billing:city', 'City', @billingAreaId, 1000, TRUE, 50, FALSE),
('billing:region_id', 'State/Province', @billingAreaId, 1100, TRUE, 50, FALSE),
('billing:postcode', 'Zip/Postal Code', @billingAreaId, 1200, TRUE, 50, FALSE),
('billing:country_id', 'Country', @billingAreaId, 1300, TRUE, 50, FALSE),
('billing:telephone', 'Telephone', @billingAreaId, 1400, FALSE, 50, FALSE),
('billing:fax', 'Fax', @billingAreaId, 1500, FALSE, 50, FALSE),
('billing:customer_password', 'Password', @billingAreaId, 1600, TRUE, 50, FALSE),
('billing:confirm_password', 'Confirm Password', @billingAreaId, 1700, TRUE, 50, FALSE),
('billing:save_in_address_book', 'Save in address book', @billingAreaId, 1800, FALSE, 100, FALSE),
('billing:use_for_shipping_yes', 'Ship to this address', @billingAreaId, 1900, FALSE, 100, FALSE),
('billing:use_for_shipping_no', 'Ship to different address', @billingAreaId, 2000, FALSE, 100, FALSE),
('billing:create_account', 'Create an account for later use', @billingAreaId, 2100, FALSE, 100, FALSE);

SET @shippingAreaId = (SELECT area_id FROM `{$this->getTable('amscheckout/area')}` WHERE area_key = 'shipping');

INSERT INTO `{$this->getTable('amscheckout/field')}` (`field_key`, `field_label`, `area_id`, `field_order`, `field_required`, `column_position`, `is_eav_attribute`) VALUES 
('shipping-address-select', 'Select a shipping address from your address book or enter a new address.', @shippingAreaId, 0, FALSE, 100, FALSE),
('shipping:prefix', 'prefix', @shippingAreaId, 100, FALSE, 20, TRUE),
('shipping:firstname', 'firstname', @shippingAreaId, 200, TRUE, 40, TRUE),
('shipping:middlename', 'middlename', @shippingAreaId, 300, FALSE, 40, TRUE),
('shipping:lastname', 'lastname', @shippingAreaId, 400, TRUE, 40, TRUE),
('shipping:suffix', 'suffix', @shippingAreaId, 500, FALSE, 20, TRUE),
('shipping:company', 'Company', @shippingAreaId, 600, FALSE, 50, FALSE),
('shipping:street1', 'Address', @shippingAreaId, 700, TRUE, 100, FALSE),
('shipping:vat_id', 'VAT Number', @shippingAreaId, 800, FALSE, 50, FALSE),
('shipping:city', 'City', @shippingAreaId, 900, TRUE, 50, FALSE),
('shipping:region_id', 'State/Province', @shippingAreaId, 1000, TRUE, 50, FALSE),
('shipping:postcode', 'Zip/Postal Code', @shippingAreaId, 1100, TRUE, 50, FALSE),
('shipping:country_id', 'Country', @shippingAreaId, 1200, TRUE, 50, FALSE),
('shipping:telephone', 'Telephone', @shippingAreaId, 1300, FALSE, 50, FALSE),
('shipping:fax', 'Fax', @shippingAreaId, 1400, FALSE, 50, FALSE),
('shipping:save_in_address_book', 'Save in address book', @shippingAreaId, 1500, FALSE, 100, FALSE),
('shipping:same_as_billing', 'Use Billing Address', @shippingAreaId, 1600, FALSE, 100, FALSE);

UPDATE `{$this->getTable('amscheckout/field')}` set
`default_field_label` = field_label,
`default_field_order` = field_order,
`default_field_required` = field_required,
`default_column_position` = column_position;


");

$this->endSetup(); 