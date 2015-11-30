
<?php

$installer = $this;
$installer->startSetup();
$installer->getConnection()->addColumn(
        $installer->getTable('pointofsale'), 'default_stock_management', "int(1) DEFAULT 1"
);
$installer->getConnection()->addColumn(
        $installer->getTable('pointofsale'), 'default_use_default_setting_for_backorder', "int(1) DEFAULT 1"
);
$installer->getConnection()->addColumn(
        $installer->getTable('pointofsale'), 'default_allow_backorder', "int(1) DEFAULT 0"
);

$installer->endSetup();
