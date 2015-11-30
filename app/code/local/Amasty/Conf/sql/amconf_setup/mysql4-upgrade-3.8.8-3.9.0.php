<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Conf
 */
$installer = $this;
$installer->startSetup();

$tableName = $this->getTable('amconf/attribute');
$fieldsSql = 'SHOW COLUMNS FROM ' . $tableName;
$cols = $this->getConnection()->fetchCol($fieldsSql);

if (!in_array('small_width', $cols))
{
    $this->run("
        ALTER TABLE `{$tableName}` 
            ADD `small_width` INT NOT NULL ,
            ADD `small_height` INT NOT NULL ,
            ADD `big_width` INT NOT NULL ,
            ADD `big_height` INT NOT NULL ,
            ADD `use_tooltip` TINYINT NOT NULL , 
            ADD `use_title` TINYINT NOT NULL , 
            
            ADD `cat_small_width` INT NOT NULL ,
            ADD `cat_small_height` INT NOT NULL ,
            ADD `cat_big_width` INT NOT NULL ,
            ADD `cat_big_height` INT NOT NULL ,
            ADD `cat_use_tooltip` TINYINT NOT NULL ;
    ");
} 
$installer->endSetup();
