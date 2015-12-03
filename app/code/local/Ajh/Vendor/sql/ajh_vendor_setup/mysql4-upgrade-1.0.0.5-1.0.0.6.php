<?php

$installer = $this;
$installer->startSetup();

$installer->removeAttribute('catalog_category', 'vendor_category_link');

$installer->endSetup();
