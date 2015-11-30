<?php
 /**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Pgrid
 */

/**
 * @var Magento_Db_Adapter_Pdo_Mysql $this
 */
$this->startSetup();

$this->run(
    "CREATE TABLE `{$this->getTable('ampgrid/grid_column')}`(
      `entity_id` INT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
      `code` VARCHAR(255) NOT NULL,
      `title` VARCHAR(255) NOT NULL,
      `column_type` VARCHAR(255) NOT NULL,
      `editable` TINYINT(5) DEFAULT 0,
      `visible` TINYINT(5) DEFAULT 1,
      PRIMARY KEY  (`entity_id`),
      KEY `IND_AM_PGRID_ORDER_CODE_COLUMN` (`code`),
      KEY `IND_AM_PGRID_ORDER_TYPE_COLUMN` (`column_type`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;"
);

$this->run(
    "CREATE TABLE `{$this->getTable('ampgrid/grid_group_column')}`(
      `group_column_id` INT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
      `column_id` int(8) UNSIGNED NOT NULL,
      `group_id` INT(8) UNSIGNED NOT NULL,
      `custom_title` VARCHAR(255) DEFAULT NULL,
      `is_editable` TINYINT(5) DEFAULT 1,
      `is_visible` TINYINT(5) DEFAULT 1,
      PRIMARY KEY  (`group_column_id`),
      UNIQUE KEY `AMASTY_COLUMN_GROUP_ID` (`column_id`, `group_id`),
      KEY `IND_AM_PGRID_ORDER_COLUMN_VISIBLE` (`is_visible`),
      CONSTRAINT `FK_AMASTY_PRODUCT_GRID_COLUMN_ID` FOREIGN KEY (`column_id`) REFERENCES `{$this->getTable('ampgrid/grid_column')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
      CONSTRAINT `FK_AMASTY_PRODUCT_GRID_GROUP_ID` FOREIGN KEY (`group_id`) REFERENCES `{$this->getTable('ampgrid/grid_group')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;"
);

$this->run(
    "CREATE TABLE `{$this->getTable('ampgrid/grid_group_attribute')}`(
      `group_attribute_id` INT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
      `attribute_id` int(8) UNSIGNED NOT NULL,
      `group_id` INT(8) UNSIGNED NOT NULL,
      `custom_title` VARCHAR(255) DEFAULT NULL,
      `is_editable` TINYINT(5) DEFAULT 1,
      PRIMARY KEY  (`group_attribute_id`),
      CONSTRAINT `FK_AMASTY_PRODUCT_GRID_ATTRIBUTE_GROUP_ID` FOREIGN KEY (`group_id`) REFERENCES `{$this->getTable('ampgrid/grid_group')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;"
);

$conn = $this->getConnection();
$conn->insertMultiple(
    $this->getTable('ampgrid/grid_column'), array(
        array(
            'code'          => 'entity_id',
            'title'         => 'ID',
            'column_type'   => 'standard',
            'editable'   => 0,
            'visible'    => 1,
        ), array(
            'code'          => 'name',
            'title'         => 'Name',
            'column_type'   => 'standard',
            'editable'   => 1,
            'visible'    => 1,
        ), array(
            'code'          => 'type',
            'title'         => 'Type',
            'column_type'   => 'standard',
            'editable'   => 0,
            'visible'    => 1,
        ), array(
            'code'          => 'set_name',
            'title'         => 'Attrib. Set Name',
            'column_type'   => 'standard',
            'editable'   => 0,
            'visible'    => 1,
        ), array(
            'code'          => 'sku',
            'title'         => 'SKU',
            'column_type'   => 'standard',
            'editable'   => 1,
            'visible'    => 1,
        ), array(
            'code'          => 'price',
            'title'         => 'Price',
            'column_type'   => 'standard',
            'editable'   => 1,
            'visible'    => 1,
        )
        , array(
            'code'          => 'qty',
            'title'         => 'Qty',
            'column_type'   => 'standard',
            'editable'   => 1,
            'visible'    => 1,
        )
        , array(
            'code'          => 'visibility',
            'title'         => 'Visibility',
            'column_type'   => 'standard',
            'editable'   => 1,
            'visible'    => 1,
        ), array(
            'code'          => 'status',
            'title'         => 'Status',
            'column_type'   => 'standard',
            'editable'   => 1,
            'visible'    => 1,
        ), array(
            'code'          => 'websites',
            'title'         => 'Websites',
            'column_type'   => 'standard',
            'editable'   => 0,
            'visible'    => 1,
        ), array(
            'code'          => 'action',
            'title'         => 'Action',
            'column_type'   => 'standard',
            'editable'   => 0,
            'visible'    => 1,
        ), array(
            'code'          => 'thumb',
            'title'         => 'Thumbnail',
            'column_type'   => 'extra',
            'editable'   => 0,
            'visible'    => 1,
        ), array(
            'code'          => 'categories',
            'title'         => 'Categories',
            'column_type'   => 'extra',
            'editable'   => 0,
            'visible'    => 1,
        ), array(
            'code'          => 'link',
            'title'         => 'Front End Product Link',
            'column_type'   => 'extra',
            'editable'   => 0,
            'visible'    => 1,
        ), array(
            'code'          => 'is_in_stock',
            'title'         => 'Availability',
            'column_type'   => 'extra',
            'editable'   => 1,
            'visible'    => 1,
        ), array(
            'code'          => 'created_at',
            'title'         => 'Creation Date',
            'column_type'   => 'extra',
            'editable'   => 0,
            'visible'    => 1,
        ), array(
            'code'          => 'qty_sold',
            'title'         => 'Qty Sold',
            'column_type'   => 'extra',
            'editable'   => 0,
            'visible'    => 1,
        ), array(
            'code'          => 'updated_at',
            'title'         => 'Last Modified Date',
            'column_type'   => 'extra',
            'editable'   => 0,
            'visible'    => 1,
        ), array(
            'code'          => 'am_special_from_date',
            'title'         => 'Special Price From',
            'column_type'   => 'extra',
            'editable'   => 1,
            'visible'    => 1,
        ), array(
            'code'          => 'am_special_to_date',
            'title'         => 'Special Price To',
            'column_type'   => 'extra',
            'editable'   => 1,
            'visible'    => 1,
        ), array(
            'code'          => 'related_products',
            'title'         => 'Related Products',
            'column_type'   => 'extra',
            'editable'   => 0,
            'visible'    => 1,
        ), array(
            'code'          => 'up_sells',
            'title'         => 'Up Sells',
            'column_type'   => 'extra',
            'editable'   => 0,
            'visible'    => 1,
        ), array(
            'code'          => 'cross_sells',
            'title'         => 'Cross Sells',
            'column_type'   => 'extra',
            'editable'   => 0,
            'visible'    => 1,
        ), array(
            'code'          => 'low_stock',
            'title'         => 'Low Stock',
            'column_type'   => 'extra',
            'editable'   => 0,
            'visible'    => 1,
        ),
    )
);

$this->endSetup();
