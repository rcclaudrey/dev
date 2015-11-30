<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Shiprules
 */

if (Mage::helper('core')->isModuleEnabled('Amasty_Methods')) {
    class Pure extends Amasty_Methods_Block_Rewrite_Checkout_Cart_Shipping
    {
    }
} else {
    class Pure extends Mage_Checkout_Block_Cart_Shipping
    {
    }
}

class Amasty_Shiprules_Block_Cart_Shipping extends Pure
{
    public function getShippingPrice($price, $flag)
    {
        return Mage::helper('amshiprules')->getShippingPrice($this, $price, $flag);
    }
}