<?php

$installer = $this;
$installer->startSetup();

$entityTypeId = $installer->getEntityTypeId('catalog_category');
$attributeSetId = $installer->getDefaultAttributeSetId($entityTypeId);

$installer->addAttributeGroup($entityTypeId, $attributeSetId, 'TMS Vendor', 110);
$attributeGroupId = $installer->getAttributeGroupId($entityTypeId, $attributeSetId, 'TMS Vendor');


$installer->addAttribute('catalog_category', 'category_link', array(
    'type' => 'text',
    'label' => 'Category Link',
    'group' => 'TMS Vendor',
    'input' => 'text',
    'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'visible' => true,
    'required' => false,
    'user_defined' => false,
    'default' => ''
));

//Add group to entity & set
$installer->addAttributeGroup('catalog_category', $attributeSetId, 'Vendor');

$installer->addAttributeToGroup(
        $entityTypeId, $attributeSetId, $attributeGroupId, 'category_link', '110'     //last Magento's attribute position in General tab is 10
);

$attributeId = $installer->getAttributeId($entityTypeId, 'category_link');

$installer->run("
INSERT INTO `{$installer->getTable('catalog_category_entity_int')}`
(`entity_type_id`, `attribute_id`, `entity_id`, `value`)
    SELECT '{$entityTypeId}', '{$attributeId}', `entity_id`, '1'
        FROM `{$installer->getTable('catalog_category_entity')}`;
");


//this will set data of your custom attribute for root category
Mage::getModel('catalog/category')
        ->load(1)
        ->setImportedCatId(0)
        ->setInitialSetupFlag(true)
        ->save();

//this will set data of your custom attribute for default category
Mage::getModel('catalog/category')
        ->load(2)
        ->setImportedCatId(0)
        ->setInitialSetupFlag(true)
        ->save();

$installer->endSetup();
