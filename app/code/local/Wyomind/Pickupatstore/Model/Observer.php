<?php

class Wyomind_PickupatStore_Model_Observer {

    function orderUpdate($observer) {
        $order = $observer->getEvent()->getOrder();
        if (strstr($order->getShippingMethod(), "pickupatstore")) {

            $storeId = substr($order->getShippingMethod(), stripos($order->getShippingMethod(), '_') + 1);
            $store = Mage::getModel('pointofsale/pointofsale')->getPlace($storeId)->getFirstItem();
            $storeDetails = $store->getName() . ' ';
            $storeDetails.=" [ ";
            $o = 0;
            if ($store->getAddress_line_1()) {
                $storeDetails.=$store->getAddress_line_1();
                $o++;
            }
            if ($store->getAddress_line_2()) {
                if ($o)
                    $storeDetails.=", ";
                $storeDetails.=$store->getAddress_line_2();
                $o++;
            }
            if ($store->getCity()) {
                if ($o)
                    $storeDetails.=", ";
                $storeDetails.=$store->getCity();
                $o++;
            }
            if ($store->getState()) {
                if ($o)
                    $storeDetails.=", ";
                $storeDetails.=$store->getState();
                $o++;
            }
            //if($store->getPostalCode())$storeDetails.=$store->getPostalCode()." ";
            $storeDetails.=" ]";

            $storeDetails.="<br>";
            $store = Mage::getModel('pointofsale/pointofsale')->getPlace($storeId)->getFirstItem();
            $storeDetails.="<br>";
            $storeDetails.=Mage::helper("pointofsale")->getHours($store->getHours(), $template = "<b>{day}</b> {H1}:{mn1} - {H2}:{mn2}<br>");

            if (Mage::getStoreConfig('carriers/pickupatstore/time')) {
                $storeDetails.="<br>" . Mage::helper('pickupatstore')->__('Your pickup time: ') . Mage::helper('pickupatstore')->formatDatetime($order->getPickupDatetime());
            } elseif (Mage::getStoreConfig('carriers/pickupatstore/date')) {
                $storeDetails.="<br>" . Mage::helper('pickupatstore')->__('Your pickup date: ') . Mage::helper('pickupatstore')->formatDate($order->getPickupDatetime());
            }
            $order->setShippingDescription($storeDetails)->save();
        }

        return;
    }

    function shippingUpdate($observer) {

        if (Mage::getStoreConfig('carriers/pickupatstore/date')) {

            $quote = $observer->getEvent()->getQuote();
            $request = $observer->getEvent()->getRequest()->getPost();

            if ($request['pickup_hour']) {
                $time = strtotime($request["pickup_day"] . ' ' . $request['pickup_hour'] . ":00");
            } else if ($request["pickup_day"]) {
                $time = strtotime($request["pickup_day"] . "00:00:00");
            }
            $quote->setPickupDatetime(Mage::getSingleton('core/date')->date($time))->save();
        }
    }

    public function addColumn(Varien_Event_Observer $observer) {

        $block = $observer->getEvent()->getBlock();
        $this->_block = $block;

        if (get_class($block) == Mage::getStoreConfig("pickupatstore/settings/grid")) {





            $block->addColumnAfter('pickup_datetime', array(
                'header' => Mage::helper('sales')->__('Pickup Date/time'),
                'index' => 'pickup_datetime',
                'type' => 'datetime',
                'width' => '150px',
                    ), 'status'
            );
        }

        return $observer;
    }

}
