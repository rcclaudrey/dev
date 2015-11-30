<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Conf
 */


$installer = new Mage_Eav_Model_Entity_Setup($this->_resourceName);
$installer->startSetup();

$installer->addAttribute('catalog_product', 'amconf_simple_price', array(
    'type'              => 'int',
    'backend'           => '',
    'frontend'          => '',
    'label'             => 'Use price of simple products',
    'note'              => 'If set to "Yes", full price of associated products of configurables is used for calculations and front end display instead of price difference.',
    'input'             => 'select',
    'class'             => '',
    'source'            => 'eav/entity_attribute_source_boolean',
    'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'visible'           => true,
    'required'          => false,
    'user_defined'      => false,
    'default'           => '',
    'searchable'        => false,
    'filterable'        => false,
    'comparable'        => false,
    'visible_on_front'  => false,
    'unique'            => false,
    'apply_to'          => 'configurable',
    'is_configurable'   => false,
//    'group'             => 'General',
));
$attributeId = $installer->getAttributeId('catalog_product', 'amconf_simple_price');

foreach ($installer->getAllAttributeSetIds('catalog_product') as $attributeSetId) 
{
    try {
        $attributeGroupId = $installer->getAttributeGroupId('catalog_product', $attributeSetId, 'General');
    } catch (Exception $e) {
        $attributeGroupId = $installer->getDefaultAttributeGroupId('catalog_product', $attributeSetId);
    }
    $installer->addAttributeToSet('catalog_product', $attributeSetId, $attributeGroupId, $attributeId);
}

$installer->endSetup();
