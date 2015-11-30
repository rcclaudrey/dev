<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Shiprules
 */
class Amasty_Shiprules_Model_Quote_Address_Rate extends Mage_Sales_Model_Quote_Address_Rate
{
    public function importShippingRate(Mage_Shipping_Model_Rate_Result_Abstract $rate)
    {
        $rateData = parent::importShippingRate($rate);
        $rateData->setOldPrice($rate->getOldPrice());
        return $rateData;
    }
}