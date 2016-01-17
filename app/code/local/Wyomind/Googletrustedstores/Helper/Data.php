<?php

class Wyomind_Googletrustedstores_Helper_Data extends Mage_Core_Helper_Data {
    
    public function isEstimatedDeliveryDateEnabled() {
        return Mage::getConfig()->getModuleConfig('Wyomind_Estimateddeliverydate')->is('active', 'true');
    }

}
