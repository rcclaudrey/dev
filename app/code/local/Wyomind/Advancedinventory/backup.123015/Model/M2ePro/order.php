<?php

class Wyomind_Advancedinventory_Model_M2ePro_Order extends Ess_M2ePro_Model_Order {

    public function afterCreateMagentoOrder() {
        
        parent::afterCreateMagentoOrder();
       
        Mage::dispatchEvent('wyomind_advancedinventory_m2epro_order_place_success', array('order' => $this->getMagentoOrder()));

      
    }

}
