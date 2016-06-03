<?php

$this->startSetup();

$this->getConnection()->addColumn(
	$this->getTable('sales/order_grid'),
	'customer_group_id',
	'smallint(6) DEFAULT NULL'
);

$this->getConnection()->addKey(
	$this->getTable('sales/order_grid'),
	'customer_group_id',
	'customer_group_id'
);

$select = $this->getConnection()
	->select()
		->join(
				array('order' => $this->getTable('sales/order')),
				'order.entity_id = order_grid.entity_id',
				array('customer_group_id' => 'customer_group_id')
			);

$this->getConnection()->query(
	$select->crossUpdateFromSelect(
		array('order_grid' => $this->getTable('sales/order_grid'))
	)
);

$this->endSetup();