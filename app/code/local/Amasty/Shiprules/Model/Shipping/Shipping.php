<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Shiprules
 */
class Amasty_Shiprules_Model_Shipping_Shipping extends Mage_Shipping_Model_Shipping
{
    /**
     * Retrieve all methods for supplied shipping data
     *
     * @param Mage_Shipping_Model_Shipping_Method_Request $data
     * @return Mage_Shipping_Model_Shipping
     */
    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        parent::collectRates($request);
        
        $result   = $this->getResult();
        Mage::dispatchEvent('am_restrict_rates', array(
            'request' => $request, 
            'result'  => $result,
        ));        
        
        
        $oldRates = $result->getAllRates();
        $oldPrices = $this->_getPrices($oldRates);
        $newRates = array();

        $validator = Mage::getSingleton('amshiprules/validator');
        $validator->init($request);
        if (!$validator->canApplyFor($oldRates)){
            return $this;
        }
            
        $validator->applyRulesTo($oldRates);
        foreach ($oldRates as $rate){
            if ($validator->needNewRequest($rate)){
                
                $newRequest = $validator->getNewRequest($rate);
                if (count($newRequest->getAllItems())){
                    
                    $result->reset();
                    parent::collectRates($newRequest);
                   
                    $rate = $validator->findRate($result->getAllRates(), $rate);
                }
                else {
                    $rate->setPrice(0);    
                }
            }
            $rate->setPrice($rate->getPrice() + $validator->getFee($rate));
            $newRates[] = $rate;
        }
        
        $result->reset();
        foreach ($newRates as $rate) {
            $rate->setOldPrice($oldPrices[$rate->getMethod()]);
            $rate->setPrice(max(0, $rate->getPrice()));
            $result->append($rate);
        }
        
        return $this;
    }

    protected function _getPrices($rates)
    {
        $prices = array();
        foreach ($rates as $rate) {
            $prices[$rate->getMethod()] = $rate->getPrice();
        }
        return $prices;
    }
}