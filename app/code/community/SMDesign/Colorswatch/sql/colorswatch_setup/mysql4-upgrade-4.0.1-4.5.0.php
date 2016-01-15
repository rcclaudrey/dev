<?php
$installer = $this;
/* @var $installer Mage_Catalog_Model_Resource_Eav_Mysql4_Setup */

$installer->startSetup();

try {
	$indexName = method_exists($installer->getConnection(), 'getIndexName') ? $installer->getConnection()->getIndexName($installer->getTable('colorswatch_images'), Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE) : 'IDX_COLORSWATCH_IMAGES_UNIQUE';
	$installer->getConnection()->dropIndex($installer->getTable('colorswatch_images'), $indexName);
	$installer->getConnection()->addIndex(
		$installer->getTable('colorswatch_images'),
		$indexName,
		array('attribute_id', 'option_id'), Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
	);
} catch (Exception $e) {
	// to do
}