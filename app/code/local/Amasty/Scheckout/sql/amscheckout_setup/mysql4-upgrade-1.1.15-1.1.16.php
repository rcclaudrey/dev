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
        ('billing:gender', 'Gender', @billingAreaId, 15, TRUE, 20, FALSE);
    ");
    
    $installer->endSetup();
?>