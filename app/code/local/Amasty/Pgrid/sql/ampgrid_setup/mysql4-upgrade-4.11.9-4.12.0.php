<?php
 /**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Pgrid
 */

/**
 * @var Magento_Db_Adapter_Pdo_Mysql $this
 */
$this->startSetup();

$conn = $this->getConnection();
$this->run(
    sprintf("UPDATE %s SET visible = 0 WHERE entity_id IN(13,14,15,18,21,22,23,24)", $this->getTable('ampgrid/grid_column'))
);

$this->run(
    sprintf("DELETE FROM %s WHERE entity_id IN(19,20)", $this->getTable('ampgrid/grid_column'))
);

$this->endSetup();
