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
        ('billing:taxvat', 'VAT Number', @billingAreaId, 900, FALSE, 50, FALSE);
        
        delete from `{$this->getTable('amscheckout/field')}` where field_key = 'billing:vat_id';
    ");
    
    $installer->endSetup();
?>