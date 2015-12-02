<?php

$this->startSetup();

$this->run('');

$setup = new Mage_Eav_Model_Entity_Setup('core_setup');

$setup->addAttribute(Mage_Catalog_Model_Category::ENTITY,
		Vikont_Fitment_Helper_Data::ARI_ACTIVITY_ATTRIBUTE_CODE,
		array(
			'group'			=> 'ARI',
			'type'			=> 'int',
			'label'			=> 'ARI activity',
			'input'			=> 'select',
			'source'		=> 'fitment/source_ari_activity',
			'backend'		=> '',
			'visible'		=> true,
			'required'		=> false,
			'visible_on_front' => false,
			'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
			'note'			=> '',
	));

$this->endSetup();