<?php

$installer = $this;
$installer->startSetup();

$installer->updateAttribute('catalog_category', 'category_link', 'default', NULL);

$installer->endSetup();
