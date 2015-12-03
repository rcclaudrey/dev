<?php
/**
 * Created by PhpStorm.
 * User: Camille
 * Date: 2/22/15
 * Time: 7:09 PM
 */

class Ajh_AjaxCheckout_Block_Cart_Minicart extends Mage_Checkout_Block_Cart_Abstract
{
    public function getSummaryCount()
    {
        if ($this->getData('summary_qty')) {
            return $this->getData('summary_qty');
        }
        return Mage::getSingleton('checkout/cart')->getSummaryQty();
    }
}