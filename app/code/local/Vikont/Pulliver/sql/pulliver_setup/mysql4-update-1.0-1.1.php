<?php

$installer = $this;
$installer->startSetup();
$installer->run("

ALTER TABLE `sku` ADD `d_motonation` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
ADD `d_bellhelm` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '';

");
$installer->endSetup();