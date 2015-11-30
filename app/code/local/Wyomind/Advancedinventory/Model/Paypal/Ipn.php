<?php

class Wyomind_Advancedinventory_Model_Paypal_Ipn extends Mage_Paypal_Model_Ipn {

    protected function _processOrder() {

       
        Mage::dispatchEvent(
                'wyomind_advancedinventory_paypal_order_place_success', array('order' => $this->_getOrder())
        );


        parent::_processOrder();
    }

}
