<?php

/**
 * Description of Data
 * @package   CueBlocks_SitemapEnhanced
 * ** @company   CueBlocks - http://www.cueblocks.com/
 
 */
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer = $this;

$installer->startSetup();

/**
 * Create table 'cb_sitemapenhanced'
 */
$installer->run("     
        -- DROP TABLE IF EXISTS {$this->getTable('sitemapEnhanced/sitemapEnhanced')};
        CREATE TABLE IF NOT EXISTS `{$this->getTable('sitemapEnhanced/sitemapEnhanced')}` (
            `sitemap_id` int(11) NOT NULL auto_increment,
            `sitemap_tot_links`   int(10) default '0',
            `sitemap_media_links` int(10) default '0',
            `sitemap_cms_links`   int(10) default '0',
            `sitemap_out_links`   int(10) default '0',
            `sitemap_prod_links`  int(10) default '0',
            `sitemap_cat_links`   int(10) default '0',
            `sitemap_tag_links`   int(10) default '0',
            `sitemap_review_links`   int(10) default '0',
            `sitemap_filename`    varchar(256) default NULL,
            `sitemap_path`        tinytext,
            `sitemap_time`        timestamp NULL default NULL,
            `store_id`            smallint(5) unsigned NOT NULL,
            INDEX par_ind (`store_id`),
            FOREIGN KEY (`store_id`) REFERENCES {$this->getTable('core_store')}(`store_id`)
            ON UPDATE CASCADE
            ON DELETE CASCADE,
            PRIMARY KEY  (`sitemap_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

        -- DROP TABLE IF EXISTS {$this->getTable('sitemapEnhanced/sitemapEnhancedFiles')};
        CREATE TABLE IF NOT EXISTS `{$this->getTable('sitemapEnhanced/sitemapEnhancedFiles')}` (
            `sitemap_file_id` int(11) NOT NULL auto_increment,
            `sitemap_file_type` varchar(32) default NULL,
            `sitemap_file_filename` varchar(256) default NULL,
            `sitemap_file_path` tinytext,
            `sitemap_id` int(11) NOT NULL,
            INDEX par_ind (`sitemap_id`),
            FOREIGN KEY (`sitemap_id`) REFERENCES {$this->getTable('sitemapEnhanced/sitemapEnhanced')}(`sitemap_id`)
            ON UPDATE CASCADE
            ON DELETE CASCADE,
            PRIMARY KEY  (`sitemap_file_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");


/*
 * NOT COMPATIBLE WITH MAGENTO < 1.6
 */
//        
//$sitemapEnhanced = $installer->getConnection()
//        ->newTable($installer->getTable('sitemapEnhanced/sitemapEnhanced'))
//        ->addColumn('sitemap_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
//            'identity' => true,
//            'unsigned' => true,
//            'nullable' => false,
//            'primary'  => true,
//                ), 'Sitemap Id')
//        ->addColumn('sitemap_links', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
//            'nullable' => false,
//            'default'  => '0',
//                ), 'Sitemap Total Links')
//        ->addColumn('sitemap_filename', Varien_Db_Ddl_Table::TYPE_TEXT, 32, array(
//                ), 'Sitemap Filename')
//        ->addColumn('sitemap_path', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
//                ), 'Sitemap Path')
//        ->addColumn('sitemap_time', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
//            'nullable' => true,
//                ), 'Sitemap Time')
//        ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
//            'unsigned' => true,
//            'nullable' => false,
//            'default'  => '0',
//                ), 'Store id')
//        ->addIndex($installer->getIdxName('sitemapEnhanced/sitemapEnhanced', array('store_id')), array('store_id'))
//        ->addForeignKey($installer->getFkName('sitemapEnhanced/sitemapEnhanced', 'store_id', 'core/store', 'store_id'), 'store_id', $installer->getTable('core/store'), 'store_id', Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
//        ->setComment('CueBlocks SitemapEnhanced');
//
//$installer->getConnection()->createTable($sitemapEnhanced);
//
//$sitemapEnhancedFile = $installer->getConnection()
//        ->newTable($installer->getTable('sitemapEnhanced/sitemapEnhancedFiles'))
//        ->addColumn('sitemap_file_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
//            'identity' => true,
//            'unsigned' => true,
//            'nullable' => false,
//            'primary'  => true,
//                ), 'Sitemap Id')
//        ->addColumn('sitemap_file_type', Varien_Db_Ddl_Table::TYPE_TEXT, 32, array(
//                ), 'Sitemap File Type')
//        ->addColumn('sitemap_file_filename', Varien_Db_Ddl_Table::TYPE_TEXT, 32, array(
//                ), 'Sitemap Filename')
//        ->addColumn('sitemap_file_path', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
//                ), 'Sitemap File Path')
//        ->addColumn('sitemap_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
//            'unsigned' => true,
//            'nullable' => false,
//            'default'  => '0',
//                ), 'Store id')
//        ->addIndex($installer->getIdxName('sitemapEnhanced/sitemapEnhancedFiles', array('sitemap_id')), array('sitemap_id'))
//        ->addForeignKey($installer->getFkName('sitemapEnhanced/sitemapEnhancedFiles', 'sitemap_id', 'sitemapEnhanced/sitemapEnhanced', 'sitemap_id'), 'sitemap_id', $installer->getTable('sitemapEnhanced/sitemapEnhanced'), 'sitemap_id', Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
//        ->setComment('CueBlocks SitemapEnhanced Files');
//
//$installer->getConnection()->createTable($sitemapEnhancedFile);

$installer->endSetup();
