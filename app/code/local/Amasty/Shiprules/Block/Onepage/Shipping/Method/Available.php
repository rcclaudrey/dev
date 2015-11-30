<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Shiprules
 */

if (Mage::helper('core')->isModuleEnabled('Amasty_Methods')) {
    class Pure extends Amasty_Methods_Block_Rewrite_Onepage_Shipping_Method_Available
    {
    }
} else {
    class Pure extends Mage_Checkout_Block_Onepage_Shipping_Method_Available
    {
    }
}

class Amasty_Shiprules_Block_Onepage_Shipping_Method_Available extends Mage_Checkout_Block_Onepage_Shipping_Method_Available
{
    public function getShippingPrice($price, $flag)
    {
        return Mage::helper('amshiprules')->getShippingPrice($this, $price, $flag);
    }
}