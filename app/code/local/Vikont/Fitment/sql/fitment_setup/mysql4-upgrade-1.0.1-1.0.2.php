<?php

$this->startSetup();

$this->run('');

$setup = new Mage_Eav_Model_Entity_Setup('core_setup');

$setup->addAttribute(Mage_Catalog_Model_Category::ENTITY,
		Vikont_Fitment_Helper_Data::ARI_HAS_FITMENT_ATTRIBUTE_CODE,
		array(
			'group'			=> 'ARI',
			'type'			=> 'int',
			'label'			=> 'Products Have Fitment',
			'input'			=> 'select',
			'source'		=> 'eav/entity_attribute_source_boolean',
			'backend'		=> '',
			'visible'		=> true,
			'required'		=> false,
			'visible_on_front' => false,
			'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
			'note'			=> '',
			'default'		=> 0,
	));

$this->endSetup();