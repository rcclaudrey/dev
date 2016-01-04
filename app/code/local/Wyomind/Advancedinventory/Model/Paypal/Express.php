<?php

class Wyomind_Advancedinventory_Model_Paypal_Express extends Mage_Paypal_Model_Express {

    protected function _placeOrder(Mage_Sales_Model_Order_Payment $payment, $amount) {

       
        Mage::dispatchEvent(
                'wyomind_advancedinventory_paypal_order_place_success', array('order' => $payment->getOrder())
        );
        parent::_placeOrder($payment, $amount);
    }

}
