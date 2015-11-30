<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Scheckout
 */

    class Amasty_Scheckout_Model_System_Config_Source_Shipping
    {
        /**
         * Options getter
         *
         * @return array
         */
        public function toOptionArray()
        {
            $hlr = Mage::helper('amscheckout');
            return array(
                array(
                    'value' => 'address', 
                    'label'=> $hlr->__('Address')
                ),
                array(
                    'value' => 'city', 
                    'label'=> $hlr->__('City')
                ),
                array(
                    'value' => 'region', 
                    'label'=> $hlr->__('State/Province')
                ),
                array(
                    'value' => 'postcode', 
                    'label'=> $hlr->__('Zip/Postal Code')
                ),
                array(
                    'value' => 'country', 
                    'label'=> $hlr->__('Country')
                ),
            );
        }

        /**
         * @return array
         */
        public function toArray()
        {
            $hlr = Mage::helper('amscheckout');
            return array(
                'address' => $hlr->__('Address'),
                'city' => $hlr->__('City'),
                'region' => $hlr->__('State/Province'),
                'postcode' => $hlr->__('Zip/Postal Code'),
                'country' => $hlr->__('Country'),
                
            );
        }
    }
?>