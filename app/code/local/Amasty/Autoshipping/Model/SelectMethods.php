<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Autoshipping
 */


class Amasty_Autoshipping_Model_SelectMethods extends Mage_Core_Model_Abstract
{
    public function toOptionArray() {
        $hlp = Mage::helper('amautoshipping');
        return array(
            'not_autoselect' => $hlp->__('Do not auto select any method'),
            'cheapest' => $hlp->__('Choose the cheapest'),
            'expensive' => $hlp->__('Choose the most expensive'),
        );
    }

    public function applyAutoShipping($allShippingRates)
    {
        $method = NULL;
        $type = Mage::getStoreConfig('amautoshipping/general/select_method');
        if ($type == 'cheapest') {
            $method = $this->_getCheapest($allShippingRates);
        } elseif ($type == 'expensive') {
            $method = $this->_getMostExpensive($allShippingRates);
        }

        return $method;
    }

    protected function _getCheapest($allShippingRates)
    {
        $cheapestMethod = NULL;
        $isFirst = true;

        foreach ($allShippingRates as $method) {
            if ($isFirst) {
                $minPrice = $method->getPrice();
                $cheapestMethod = $method->getCode();
                $isFirst = false;
            } else {
                if ($method->getPrice() < $minPrice) {
                    $minPrice = $method->getPrice();
                    $cheapestMethod = $method->getCode();
                }
            }
        }

        return $cheapestMethod;
    }

    protected function _getMostExpensive($allShippingRates)
    {
        $mostExpensiveMethod = NULL;
        $isFirst = true;

        foreach ($allShippingRates as $method) {
            if ($isFirst) {
                $maxPrice = $method->getPrice();
                $mostExpensiveMethod = $method->getCode();
                $isFirst = false;
            } else {
                if ($method->getPrice() > $maxPrice) {
                    $maxPrice = $method->getPrice();
                    $mostExpensiveMethod = $method->getCode();
                }
            }
        }

        return $mostExpensiveMethod;
    }
}
