<?php
 /**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Sorting
 */

class Amasty_Sorting_Model_Source_Productattribute
{
    public function toOptionArray()
    {
        $entityTypeId = Mage::getModel('eav/entity')->setType(Mage_Catalog_Model_Product::ENTITY)->getTypeId();
        $attributes = Mage::getModel('eav/entity_attribute')->getCollection();
        $attributes->addFieldToFilter('entity_type_id', $entityTypeId);
        $attributes->addFieldToFilter('backend_type', 'decimal');

        $options = array();
        foreach($attributes as $attribute) {
            $options[] = array(
                'value' => $attribute->getAttributeCode(),
                'label' => $attribute->getFrontendLabel(),
            );
        }
        return $options;
    }
}