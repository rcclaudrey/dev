<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Fpccrawler
 */


/**
 * @author Amasty
 */
class Amasty_Fpccrawler_Model_Source_Groups extends Mage_Core_Model_Config_Data
{
    public function toOptionArray($addEmpty = true)
    {
        $collection = Mage::getModel('customer/group')->getCollection();
        $options    = array();
        foreach ($collection as $category) {
            if ($category->getCustomerGroupCode() != "" && $category->getCustomerGroupCode() != "NOT LOGGED IN") {
                $options[] = array(
                    'label' => $category->getCustomerGroupCode(),
                    'value' => $category->getCustomerGroupId()
                );
            }
        }

        return $options;
    }
}