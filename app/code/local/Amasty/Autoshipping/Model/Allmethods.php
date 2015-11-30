<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Autoshipping
 */


class Amasty_Autoshipping_Model_Allmethods
{
    public function toOptionArray($isActiveOnlyFlag=false)
    {
        $methods = array(array('value'=>'', 'label'=>''));
        $carriers = Mage::getSingleton('shipping/config')->getAllCarriers();
        foreach ($carriers as $carrierCode=>$carrierModel) {
            if (!$carrierModel->isActive() && (bool)$isActiveOnlyFlag === true) {
                continue;
            }
            if (get_class($carrierModel) == 'Webshopapps_Matrixrate_Model_Carrier_Matrixrate') {
                $collectionData = Mage::getResourceModel('matrixrate_shipping/carrier_matrixrate_collection')->getData();
                $carrierMethods = array();
                for ($i = 0; $i < count($collectionData); $i++) {
                    $carrierMethods['matrixrate_' . $collectionData[$i]['pk']] = $collectionData[$i]['delivery_type'];
                }
            } else {
                $carrierMethods = $carrierModel->getAllowedMethods();
            }
            if (!$carrierMethods) {
                continue;
            }
            $carrierTitle = Mage::getStoreConfig('carriers/'.$carrierCode.'/title');
            $methods[$carrierCode] = array(
                'label'   => $carrierTitle,
                'value' => array(),
            );
            foreach ($carrierMethods as $methodCode=>$methodTitle) {
                $methods[$carrierCode]['value'][] = array(
                    'value' => $carrierCode.'_'.$methodCode,
                    'label' => '['.$carrierCode.'] '.$methodTitle,
                );
            }
        }

        return $methods;
    }
}
