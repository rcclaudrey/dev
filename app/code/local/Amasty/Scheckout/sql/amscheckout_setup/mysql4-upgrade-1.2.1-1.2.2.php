<?php
    /**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Scheckout
 */
    $installer = $this;
    $installer->startSetup();
    
    $this->run("
        SET @billingAreaId = (SELECT area_id FROM `{$this->getTable('amscheckout/area')}` WHERE area_key = 'billing');

        INSERT INTO `{$this->getTable('amscheckout/field')}` (`field_key`, `field_label`, `area_id`, `field_order`, `field_required`, `column_position`, `is_eav_attribute`) VALUES 
        ('billing:datebirth', 'Date of Birth', 1, 2200, FALSE, 100, FALSE);
        
        UPDATE `{$this->getTable('amscheckout/field')}` set
        `default_field_label` = field_label,
        `default_field_order` = field_order,
        `default_field_required` = field_required,
        `default_column_position` = column_position
        WHERE field_key IN ('billing:datebirth') ;
        
    ");
    
    $installer->endSetup();
?>