<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Sorting
 */
class Amasty_Sorting_Model_Catalog_Config extends Mage_Catalog_Model_Config
{
    /**
     * Adds new custom options
     *
     * @return array
     */    
    protected function addNewOptions($arr, $asAttributes)
    {
        $options = array();
        
        $methods = Mage::helper('amsorting')->getMethods();
        foreach ($methods as $className){
            $method = Mage::getSingleton('amsorting/method_' . $className);
            if ($method->isEnabled()){
                $options[$method->getCode()] = Mage::helper('amsorting')->__($method->getName());    
            }
        }        

        if ($asAttributes){
            foreach ($options as $k=>$v){
                $options[$k] = array(
                    'attribute_code' => $k,
                    'frontend_label' => $v,    
                );    
            }
        }
        
        return array_merge($arr, $options);     
    }
    
    /**
     * Retrieve Attributes array used for sort by
     *
     * @return array
     */
    public function getAttributesUsedForSortBy() 
    {
        $options = parent::getAttributesUsedForSortBy();
        return $this->addNewOptions($options, true);
    }

    /**
     * Retrieve Attributes Used for Sort by as array
     * key = code, value = name
     *
     * @return array
     */
    public function getAttributeUsedForSortByArray()
    {
        $options = array();
        if (!Mage::getStoreConfig('amsorting/general/hide_best_value')){
            $options['position'] = Mage::helper('catalog')->__('Position');
        };   
        foreach ($this->getAttributesUsedForSortBy() as $attribute) {
            $title = !empty($attribute['store_label']) ? $attribute['store_label'] : $attribute['frontend_label'];
            $options[$attribute['attribute_code']] = $title;
        }
        return $this->addNewOptions($options, false);
    }
}