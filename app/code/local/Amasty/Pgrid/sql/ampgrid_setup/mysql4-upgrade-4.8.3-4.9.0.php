<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Pgrid
 */

$this->startSetup();

Mage::helper('ampgrid')->addNoticeIndex();

$this->run("
CREATE TABLE `{$this->getTable('ampgrid/qty_sold')}` (
  `product_id`  int(10)     unsigned NOT NULL,
  `qty_sold` int(10)     unsigned NOT NULL,
  CONSTRAINT `FK_AMPGRID_QTY_SOLD_PRODUCT_ID_CATALOG_PRODUCT_ENTITY_ENTITY_ID` FOREIGN KEY (`product_id`) REFERENCES `{$this->getTable('catalog/product')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDb;
");

$this->endSetup(); 