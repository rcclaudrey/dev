<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Scheckout
 */

    class Amasty_Scheckout_Model_System_Config_Source_Payment_Allowedmethods extends Mage_Adminhtml_Model_System_Config_Source_Payment_Allowedmethods
    {
        public function toOptionArray()
        {
            $ret = array();
            $payments = Mage::getSingleton('payment/config')->getActiveMethods();
            foreach ($payments as $paymentCode=>$paymentModel) {
                $paymentTitle = Mage::getStoreConfig('payment/'.$paymentCode.'/title');
                $ret[$paymentCode] = array(
                    'label'   => $paymentTitle,
                    'value' => $paymentCode,
                );
            }

            $ret = array_merge(array(
                "default" => array(
                    "label" => "Auto-select",
                    "value" => array(
                        array(
                            "value" => "none",
                            "label" => "None",
                        ),
                        array(
                            "value" => "",
                            "label" => "First available",
                        ),
                    )
                )
            ), $ret);
            return $ret;
        }
    }
?>