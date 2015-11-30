<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Conf
 */
$this->startSetup();

$this->run("
CREATE TABLE IF NOT EXISTS `{$this->getTable('amconf/swatch')}` (
  `attribute_id` int(10) unsigned NOT NULL,
  `color` varchar(255) NULL,
  `extension` varchar(255) NULL
) ENGINE=InnoDb;
");

$this->endSetup();