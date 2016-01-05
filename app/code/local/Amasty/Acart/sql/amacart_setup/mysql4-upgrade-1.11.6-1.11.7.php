<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Acart
 */

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$this->startSetup();

$this->run("
    ALTER TABLE `{$this->getTable('amacart/canceled')}`
    drop foreign key FK_am_acart_canceled_sales_flat_quote;
");

$this->run("
    ALTER TABLE `{$this->getTable('amacart/history')}`
    drop foreign key FK_am_acart_history_am_acart_canceled,
    drop foreign key FK_am_acart_history_am_acart_rule,
    drop foreign key FK_am_acart_history_core_store,
    drop foreign key FK_am_acart_history_am_acart_schedule,
    drop foreign key FK_am_acart_history_customer_entity,
    drop foreign key FK_am_acart_history_salesrule,
    drop foreign key FK_am_acart_history_sales_flat_quote;
");

$this->endSetup();