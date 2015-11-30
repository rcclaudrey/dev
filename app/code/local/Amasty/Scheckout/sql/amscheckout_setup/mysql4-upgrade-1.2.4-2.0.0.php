<?php
    /**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Scheckout
 */
    $installer = $this;
    $installer->startSetup();
    
    $this->run("
        DELETE FROM `{$this->getTable('amscheckout/field')}` 
        WHERE `field_key` IN ('billing-address-select', 'shipping-address-select', 'billing:create_account');
        

        UPDATE  `{$this->getTable('amscheckout/field')}` 
            set field_key = 'billing:street'
            where field_key in ('billing:street1');
        
        UPDATE  `{$this->getTable('amscheckout/field')}` 
            set field_key = 'shipping:street'
            where field_key in ('shipping:street1');
        
    ");
    
    $installer->endSetup();
?>