<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Acart
 */

$this->startSetup();

$this->run("
    alter table `{$this->getTable('amacart/history')}`
    change column schedule_id `schedule_id` INT(10) UNSIGNED DEFAULT NULL;

    alter table `{$this->getTable('amacart/history')}`
    DROP FOREIGN KEY `FK_am_acart_history_am_acart_schedule`;

    alter table `{$this->getTable('amacart/history')}`
    add CONSTRAINT `FK_am_acart_history_am_acart_schedule` FOREIGN KEY (`schedule_id`) REFERENCES `{$this->getTable('amacart/schedule')}` (`schedule_id`) ON DELETE SET NULL;

    alter table `{$this->getTable('amacart/history')}`
    add column `rule_id` INT(10) UNSIGNED DEFAULT NULL after quote_id;

    alter table `{$this->getTable('amacart/history')}`
    add CONSTRAINT `FK_am_acart_history_am_acart_rule` FOREIGN KEY (`rule_id`) REFERENCES `{$this->getTable('amacart/rule')}` (`rule_id`) ON DELETE SET NULL;

    update `{$this->getTable('amacart/history')}` h
    inner join `{$this->getTable('amacart/schedule')}` s on s.schedule_id = h.schedule_id
    set h.rule_id = s.rule_id;
");

$this->endSetup(); 