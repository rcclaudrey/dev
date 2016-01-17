<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Xsearch
 */  
class Amasty_Xsearch_Model_Source_Attributes
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = array(array('value' => '', 'label'=> '-'));
        
        $collection = Mage::getResourceModel('eav/entity_attribute_collection')
            ->setItemObjectClass('catalog/resource_eav_attribute')
            ->setEntityTypeFilter(Mage::getResourceModel('catalog/product')->getTypeId());
        
        foreach ($collection as $attribute) {
            $label = $attribute->getFrontendLabel();
            if ($label){ // skip system and `exclude` attributes
                $options[] = array(
                    'value' => $attribute->getAttributeCode(),
                    'label' => $label
                );
            }
        }
        
        return $options;
//        
//        return array(
//            array('value' => 1, 'label'=> '1'),
//            array('value' => 0, 'label'=> '0'),
//        );
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $arr  = array(array('' => '-'));
        $optionArray = $this->toOptionArray();
        foreach($optionArray as $option){
            $arr[$option['value']] = $option['label'];
        }
        
//        return array(
//            0 => Mage::helper('adminhtml')->__('No'),
//            1 => Mage::helper('adminhtml')->__('Yes'),
//        );
    }

}
