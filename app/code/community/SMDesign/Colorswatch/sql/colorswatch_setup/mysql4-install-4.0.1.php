<?php

$installer = $this;

$installer->startSetup();

$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('colorswatch_images')};
CREATE TABLE {$this->getTable('colorswatch_images')} (
  `entity_id` int(11) unsigned NOT NULL auto_increment,
  `attribute_code` varchar(255) NOT NULL default '',
  `attribute_id` int(11)  NOT NULL default '0',
  `option_id` int(11)  NOT NULL default '0',
  `swatch_description` varchar(255) NOT NULL default '',
  `image_base` varchar(255) NOT NULL default '',
  `image_active` varchar(255) NOT NULL default '',
  `image_hover` varchar(255) NOT NULL default '',
  `image_disabled` varchar(255) NOT NULL default '',
  `created_time` datetime NULL,
  `update_time` datetime NULL,
  PRIMARY KEY (`entity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- DROP TABLE IF EXISTS {$this->getTable('colorswatch_attribute_settings')};
CREATE TABLE {$this->getTable('colorswatch_attribute_settings')} (
 	`entity_id` int(11) unsigned NOT NULL auto_increment,
  `attribute_id` int(11)  NOT NULL default '0',
  `attribute_code` varchar(255) NOT NULL default '',
  `key` varchar(255) NOT NULL default '',
	`value` varchar(255) NOT NULL default '',
  PRIMARY KEY (`entity_id`),
  KEY (`attribute_id`, `key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



");

$attributeInstaller = new Mage_Eav_Model_Entity_Setup('core_setup');
$attributeInstaller->addAttribute('catalog_product', 'use_smd_colorswatch', array(
    'group'                    => 'ColorSwatch',
    'type'                     => 'int',
    'input'                    => 'select',
    'label'                    => 'Enable SMDesign ColorSwatch',
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
    'default'                   => 1,
    'unique'                   => false,
    'user_defined'             => false,
    'is_user_defined'          => false,
    'used_in_product_listing'  => true
));
$attributeInstaller->updateAttribute('catalog_product', 'use_smd_colorswatch', 'apply_to', join(',', array('configurable')));



$installer->endSetup(); 