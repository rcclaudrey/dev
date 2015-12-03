<?php
/**
 * Celebros Qwiser - Magento Extension
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish correct extension functionality.
 * If you wish to customize it, please contact Celebros.
 *
 * @category    Celebros
 * @package     Celebros_Celexport
 */
$installer = $this;

$installer->startSetup();

$installer->run("

    DROP TABLE IF EXISTS {$this->getTable('celebros_cache')};
    CREATE TABLE {$this->getTable('celebros_cache')} (
      `cache_id` int(11) NOT NULL auto_increment,
      `name` varchar(255) NULL,
      `content` longblob,
      PRIMARY KEY (`cache_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    CREATE TABLE IF NOT EXISTS `{$this->getTable('celebros_mapping')}` (
        `id` int(11) NOT NULL auto_increment,
        `xml_field` VARCHAR(255) NULL,
        `code_field` VARCHAR(255),
        PRIMARY KEY  (`id`),
        UNIQUE `CODE_FIELD` ( `code_field` )
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    
    INSERT IGNORE INTO `{$this->getTable('celebros_mapping')}`  (id, xml_field, code_field)
        VALUES
        (null,'title','title'),
        (null, 'link', 'link'),
        (null, 'status','status'),
        (null, 'image_link','image_link'),
        (null, 'thumbnail_label','thumbnail_label'),
        (null, 'rating','rating'),
        (null, 'short_description','short_description'),
        (null, 'mag_id', 'id'),
        (null, 'visible', 'visible'),
        (null, 'store_id', 'store_id'),
        (null, 'is_in_stock', 'is_in_stock'),
        (null, 'sku', 'sku'),
        (null, 'category', 'category'),
        (null, 'websites', 'websites'),
        (null, 'news_from_date', 'news_from_date'),
        (null, 'news_to_date', 'news_to_date');
");

//Setting the default export path according to the server's root address.
$resource = new Mage_Core_Model_Config();
$resource->saveConfig('celexport/export_settings/path', Mage::getBaseDir('base').'/var/celebros/export', 'default', 0);

$installer->endSetup(); 