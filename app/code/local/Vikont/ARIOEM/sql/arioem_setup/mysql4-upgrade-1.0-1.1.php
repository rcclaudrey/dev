<?php

$this->startSetup();

$installer = new Mage_Customer_Model_Entity_Setup();

$installer->getConnection()
		->addColumn($installer->getTable('customer_group'), 'cost_percent', array(
				'type' => Varien_Db_Ddl_Table::TYPE_FLOAT,
				'nullable' => true,
				'default' => 0.0,
				'comment' => 'Default cost addition for dealers',
			));

$this->endSetup();