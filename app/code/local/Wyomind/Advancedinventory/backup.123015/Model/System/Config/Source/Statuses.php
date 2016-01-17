<?php

class Wyomind_Advancedinventory_Model_System_Config_Source_Statuses {

    public function toOptionArray() {



        foreach (array_merge(Mage::getSingleton('sales/order_config')->getVisibleOnFrontStates(), Mage::getSingleton('sales/order_config')->getInvisibleOnFrontStates()) as $key => $state) {

            foreach (Mage::getSingleton('sales/order_config')->getStateStatuses($state) as $k => $s) {
                $data[] = array('value' => $k, 'label' => $s);
            }
        }

        return $data;
    }

    public function toArray() {
        return $this->toOptionArray();
    }

}
