<?php
 /**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Sorting
 */

/* @var $this Mage_Core_Model_Resource_Setup */
$this->startSetup();

/**
 * create table Notification
 */
$table = $this
   ->getConnection()
   ->newTable($this->getTable('amsorting/revenue'))
   ->addColumn(
       'id', Varien_Db_Ddl_Table::TYPE_INTEGER,
       null, array(
       'identity' => true,
       'unsigned' => true,
       'nullable' => false,
   ), 'Id'
   )
   ->addColumn(
       'store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null,
       array(
           'unsigned' => true,
           'nullable' => false,
           'default'  => '0.0000',
       ), 'Store Id'
   )
   ->addColumn(
       'revenue', Varien_Db_Ddl_Table::TYPE_DECIMAL, '9,4',
       array(
           'unsigned' => true,
           'nullable' => false,
       ), 'Revenue'
   )
   ->addIndex(
       $this->getIdxName(
           'amsorting/revenue', array('revenue')
       ),
       array('id', 'store_id')
   )
   ->setComment('Index Table revenue');

$this->getConnection()->createTable($table);

$this->endSetup();