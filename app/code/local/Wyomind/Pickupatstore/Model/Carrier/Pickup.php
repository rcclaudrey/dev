<?php

class Wyomind_Pickupatstore_Model_Carrier_Pickup extends Mage_Shipping_Model_Carrier_Abstract {

    /**
     * unique internal shipping method identifier
     *
     * @var string [a-z0-9_]
     */
    protected $_code = 'pickupatstore';

    /**
     * Collect rates for this shipping method based on information in $request
     *
     * @param Mage_Shipping_Model_Rate_Request $data
     * @return Mage_Shipping_Model_Rate_Result
     */
    public function collectRates(Mage_Shipping_Model_Rate_Request $request) {

        // skip if not enabled
        if (!Mage::getStoreConfig('carriers/' . $this->_code . '/active')) {
            return false;
        }

        if (!Mage::getConfig()->getModuleConfig("Wyomind_Pointofsale")->is("active", "true")) {
            return false;
        }

        // get necessary configuration values
        $handling_fee = Mage::getStoreConfig('carriers/' . $this->_code . '/handling_fee');

        // this object will be returned as result of this method
        // containing all the shipping rates of this method
        $result = Mage::getModel('shipping/rate_result');
        $store_id = Mage::app()->getStore()->getStoreId();

        $pos = Mage::app()->getLayout()->createBlock('pointofsale/pointofsale');

        if (!Mage::app()->getStore()->isAdmin())
            $places = $pos->getPointofsale();
        else
            $places = Mage::getModel('pointofsale/pointofsale')->getCollection();



        $_places = $places;
        $ai_enabled = Mage::helper("core")->isModuleEnabled("Wyomind_Advancedinventory");
        if ($ai_enabled) {
            $ai_helper = Mage::helper("advancedinventory");
            $_places = $ai_helper->getPickupPlaces($_places);
        }


        foreach ($_places as $store) {

            if ($store->getStatus() || !$store_id) {
                // create new instance of method rate
                $method = Mage::getModel('shipping/rate_result_method');

                // record carrier information
                $method->setCarrier($this->_code);
                $method->setCarrierTitle(Mage::getStoreConfig('carriers/' . $this->_code . '/title'));

                // record method information
                $method->setMethod($store->getPlaceId());
                $address = $store->getName() . ' ';
                $address.=" [ ";
                $o = 0;
                if ($store->getAddress_line_1()) {
                    $address.=$store->getAddress_line_1();
                    $o++;
                }
                if ($store->getAddress_line_2()) {
                    if ($o)
                        $address.=", ";
                    $address.=$store->getAddress_line_2();
                    $o++;
                }
                if ($store->getCity()) {
                    if ($o)
                        $address.=", ";
                    $address.=$store->getCity();
                    $o++;
                }
                if ($store->getState()) {
                    if ($o)
                        $address.=", ";
                    $address.=$store->getState();
                    $o++;
                }
                //if($store->getPostalCode())$address.=$store->getPostalCode()." ";
                $address.=" ]";


                if (!in_array(Mage::app()->getRequest()->getActionName(), array("saveBilling", "saveShipping"))) {
                    $quote = Mage::getSingleton('checkout/session')->getQuote();
                    if (Mage::getSingleton('core/session')->getPickupatstore() && Mage::getStoreConfig('carriers/pickupatstore/date') && Mage::getStoreConfig('carriers/pickupatstore/time'))
                        $address.=" " . Mage::helper('pickupatstore')->__('Your pickup time: ') . Mage::helper('pickupatstore')->formatDatetime($quote->getPickupDatetime()) . " ";

                    elseif (Mage::getSingleton('core/session')->getPickupatstore() && Mage::getStoreConfig('carriers/pickupatstore/date'))
                        $address.=" " . Mage::helper('pickupatstore')->__('Your pickup date: ') . Mage::helper('pickupatstore')->formatDate($quote->getPickupDatetime()) . " ";
                }

                $method->setMethodTitle($address);

                $method->setPrice($handling_fee);


                // add this rate to the result
                $result->append($method);
            }
        }
     
        if ($ai_enabled)
            Mage::log($ai_helper->log, null, "Pickup@Store.log");

        return $result;
    }

    /**
     * This method is used when viewing / listing Shipping Methods with Codes programmatically
     */
    public function getAllowedMethods() {
        $stores = Mage::getModel('pointofsale/pointofsale')->getPlaces();
        foreach ($stores as $store) {
            $arr[$store->getPlaceId()] = $store->getName();
        }

        return $arr;
    }

}
