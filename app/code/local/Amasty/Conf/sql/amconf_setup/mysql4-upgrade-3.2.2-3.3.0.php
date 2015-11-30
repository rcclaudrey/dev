<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Conf
 */
$installer = $this;
$installer->startSetup();

$installer->run("
    CREATE TABLE IF NOT EXISTS `{$this->getTable('amconf/product_attribute')}` (
    `product_attribute_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `product_super_attribute_id` INT(10) UNSIGNED NOT NULL COMMENT 'Product Super Attribute ID',  
    `use_image_from_product` TINYINT(1) UNSIGNED NOT NULL DEFAULT FALSE,
    PRIMARY KEY (`product_attribute_id`),
    KEY `IDX_AMASTY_CATALOG_PRODUCT_SUPER_ATTRIBUTE_PRODUCT_ID` (`product_super_attribute_id`),
    CONSTRAINT `FK_AMASTLY_CAT_PRD_SPR_ATTR_PRD_ID_CAT_PRD_ENTT_ENTT_ID` FOREIGN KEY (`product_super_attribute_id`) REFERENCES `{$this->getTable('catalog/product_super_attribute')}` (`product_super_attribute_id`) ON DELETE CASCADE
  ) ENGINE=INNODB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
");

$installer->endSetup();