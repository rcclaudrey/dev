<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Acart
 */

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$templateCode = 'amacart_template_main_template';

$configValuesMap = array(
  'amacart/template/main_template' => $templateCode
);

foreach ($configValuesMap as $configPath=>$configValue) {
    $installer->setConfigData($configPath, $configValue);
}

//$locale = 'en_US';
//    
//$template = Mage::getModel('adminhtml/email_template');
//
//$template->loadDefault($templateCode, $locale);
//$template->setData('orig_template_code', $templateCode);
//$template->setData('template_variables', Zend_Json::encode($template->getVariablesOptionArray(true)));
//
//$template->setData('template_code', Amasty_Acart_Model_Schedule::DEFAULT_TEMPLATE_CODE);
//
//$template->setTemplateType(Mage_Core_Model_Email_Template::TYPE_HTML);
//
//$template->setId(NULL);
//
//$template->save();


$this->startSetup();

$this->run("
 CREATE TABLE `{$this->getTable('amacart/rule')}` (
  `rule_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT '',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `priority` int(10) unsigned NOT NULL DEFAULT 1,
  `stores` varchar(255) NOT NULL DEFAULT '',
  `cust_groups` varchar(255) NOT NULL DEFAULT '',
  `conditions_serialized` text,
  `cancel_rule` enum('bought','link') DEFAULT NULL,
  PRIMARY KEY (`rule_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE `{$this->getTable('amacart/schedule')}` (
  `schedule_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `rule_id` int(10) unsigned NOT NULL,
  `email_template_id` int(10) unsigned DEFAULT NULL,
  `delayed_start` int(10) unsigned DEFAULT NULL,
  `coupon_type` enum('by_percent','by_fixed','cart_fixed') DEFAULT NULL,
  `discount_amount` int(5) unsigned NOT NULL DEFAULT '0',
  `expired_in_days` INT(5) UNSIGNED NOT NULL DEFAULT '0',
  `subtotal_greater_than` DECIMAL(12,4) DEFAULT NULL,
  PRIMARY KEY (`schedule_id`),
  KEY `FK_am_acart_schedule_am_acart_rules` (`rule_id`),
  KEY `FK_am_acart_schedule_core_email_template` (`email_template_id`),
  CONSTRAINT `FK_am_acart_schedule_core_email_template` FOREIGN KEY (`email_template_id`) REFERENCES `{$this->getTable('core/email_template')}` (`template_id`) ON DELETE SET NULL,
  CONSTRAINT `FK_am_acart_schedule_am_acart_rules` FOREIGN KEY (`rule_id`) REFERENCES `{$this->getTable('amacart/rule')}` (`rule_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE `{$this->getTable('amacart/blacklist')}` (
  `blacklist_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `email` char(255) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`blacklist_id`),
  UNIQUE KEY `email` (`email`),
  KEY `IDX_EMAIL` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `{$this->getTable('amacart/history')}` (
  `history_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `quote_id` int(10) unsigned NOT NULL,
  `schedule_id` int(10) unsigned NOT NULL,
  `canceled_id` int(10) unsigned DEFAULT NULL,
  `store_id` smallint(5) unsigned DEFAULT NULL,
  `email` char(255) DEFAULT NULL,
  `customer_id` int(10) unsigned DEFAULT NULL,
  `customer_name` VARCHAR(255) DEFAULT NULL,
  `body` text,
  `subject` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `scheduled_at` timestamp NULL DEFAULT NULL,
  `executed_at` timestamp NULL DEFAULT NULL,
  `finished_at` timestamp NULL DEFAULT NULL,
  `status` enum('pending','processing','sent','done','blacklist') DEFAULT NULL,
  `public_key` char(255) DEFAULT NULL,
  `sales_rule_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`history_id`),
  KEY `FK_am_acart_history_am_acart_schedule` (`schedule_id`),
  KEY `FK_am_acart_history_sales_flat_quote` (`quote_id`),
  KEY `IDX_STATUS` (`status`),
  KEY `IDX_EMAIL` (`email`),
  KEY `FK_am_acart_history_am_acart_canceled` (`canceled_id`),
  KEY `FK_am_acart_history_customer_entity` (`customer_id`),
  KEY `FK_am_acart_history_core_store` (`store_id`),
  KEY `FK_am_acart_history_salesrule` (`sales_rule_id`),
  CONSTRAINT `FK_am_acart_history_am_acart_canceled` FOREIGN KEY (`canceled_id`) REFERENCES `{$this->getTable('amacart/canceled')}` (`canceled_id`) ON DELETE CASCADE,
  CONSTRAINT `FK_am_acart_history_am_acart_schedule` FOREIGN KEY (`schedule_id`) REFERENCES `{$this->getTable('amacart/schedule')}` (`schedule_id`) ON DELETE CASCADE,
  CONSTRAINT `FK_am_acart_history_core_store` FOREIGN KEY (`store_id`) REFERENCES `{$this->getTable('core/store')}` (`store_id`) ON DELETE CASCADE,
  CONSTRAINT `FK_am_acart_history_customer_entity` FOREIGN KEY (`customer_id`) REFERENCES `{$this->getTable('customer/entity')}` (`entity_id`) ON DELETE CASCADE,
  CONSTRAINT `FK_am_acart_history_salesrule` FOREIGN KEY (`sales_rule_id`) REFERENCES `{$this->getTable('salesrule/rule')}` (`rule_id`) ON DELETE SET NULL,
  CONSTRAINT `FK_am_acart_history_sales_flat_quote` FOREIGN KEY (`quote_id`) REFERENCES `{$this->getTable('sales/quote')}` (`entity_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE `{$this->getTable('amacart/canceled')}` (
  `canceled_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `quote_id` int(10) unsigned NOT NULL,
  `history_id` int(10) unsigned DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `reason` enum('elapsed','bought','link','blacklist','admin') DEFAULT NULL,
  PRIMARY KEY (`canceled_id`),
  UNIQUE KEY `quote_id` (`quote_id`),
  KEY `IDX_REASON` (`reason`),
  KEY `FK_am_acart_canceled_sales_flat_quote` (`quote_id`),
  KEY `FK_am_acart_canceled_am_acart_history` (`history_id`),
  CONSTRAINT `FK_am_acart_canceled_am_acart_history` FOREIGN KEY (`history_id`) REFERENCES `{$this->getTable('amacart/history')}` (`history_id`) ON DELETE SET NULL,
  CONSTRAINT `FK_am_acart_canceled_sales_flat_quote` FOREIGN KEY (`quote_id`) REFERENCES `{$this->getTable('sales/quote')}` (`entity_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `{$this->getTable('amacart/quote2email')}` (
  `quote2email_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `quote_id` int(10) unsigned NOT NULL,
  `email` char(255) DEFAULT NULL,
  PRIMARY KEY (`quote2email_id`),
  UNIQUE KEY `email` (`quote_id`,`email`),
  KEY `IDX_EMAIL` (`email`),
  CONSTRAINT `FK_am_acart_quote2email_sales_flat_quote` FOREIGN KEY (`quote_id`) REFERENCES `{$this->getTable('sales/quote')}` (`entity_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `{$this->getTable('amacart/attribute')}` (
  `attr_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `rule_id` INT(10) UNSIGNED NOT NULL,
  `code` VARCHAR(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`attr_id`),
  KEY `FK_ACART_RULE` (`rule_id`),
  CONSTRAINT `FK_ACART_RULE` FOREIGN KEY (`rule_id`) REFERENCES `{$this->getTable('amacart/rule')}` (`rule_id`) ON DELETE CASCADE
) ENGINE=INNODB DEFAULT CHARSET=utf8

");

$this->endSetup(); 