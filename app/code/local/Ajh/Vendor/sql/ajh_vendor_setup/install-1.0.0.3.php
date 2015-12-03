<?php

$installer = $this;
$installer->startSetup();

// Add new Attribute group
$groupName = 'Vendor';
$entityTypeId = $installer->getEntityTypeId('catalog_product');
$attributeSetId = $installer->getDefaultAttributeSetId($entityTypeId);
$installer->addAttributeGroup($entityTypeId, $attributeSetId, $groupName, 100);
$attributeGroupId = $installer->getAttributeGroupId($entityTypeId, $attributeSetId, $groupName);

// Add existing attribute to group
$attributeId = $installer->getAttributeId($entityTypeId, 'vendor_category_link');
$installer->addAttributeToGroup($entityTypeId, $attributeSetId, $attributeGroupId, $attributeId, null);

$installer->endSetup();


