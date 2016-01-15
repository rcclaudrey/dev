<?php
$installer = $this;
$installer->startSetup();

$attributeInstaller = new Mage_Eav_Model_Entity_Setup('core_setup');
$attributeInstaller->addAttribute('catalog_product', 'enable_zoom_plugin', array(
    'group'                    => 'ColorSwatch',
    'type'                     => 'int',
    'input'                    => 'select',
    'label'                    => 'Enable SMDesign Zoom',
    'global'                   => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'visible'                  => 1,
    'required'                 => 0,
    'visible_on_front'         => 0,
    'is_html_allowed_on_front' => 0,
    'is_configurable'          => 0,
    'source'                   => 'eav/entity_attribute_source_boolean',
    'searchable'               => 0,
    'filterable'               => 0,
    'comparable'               => 0,
    'default'                  => 1,
    'unique'                   => false,
    'user_defined'             => false,
    'is_user_defined'          => false,
    'used_in_product_listing'  => true
));
$attributeInstaller->updateAttribute('catalog_product', 'enable_zoom_plugin', 'apply_to', join(',', array('simple','grouped','configurable','virtual','bundle','downloadable')));

$installer->endSetup(); 