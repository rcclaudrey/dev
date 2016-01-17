<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Xsearch
 */
class Amasty_Xsearch_Helper_Data extends Mage_Core_Helper_Abstract
{
 
    function substr($val, $max = 100){
        $ret = $val;
        
        if (function_exists("mb_strlen") && mb_strlen($val, 'UTF-8') > $max) {
            $max -= mb_strlen('...', 'UTF-8');
            $ret = mb_substr($val, 0, $max, 'UTF-8').'...';	
    	} else if (strlen($val) > $max){
            $max -= strlen('...');
            $ret = substr($val, 0, $max).' ...';
        }
        
        return $ret;
    }
    
    public function getAddToCartUrl($product, $additional = array())
    {
        if (!$product->getTypeInstance(true)->hasRequiredOptions($product)) {
            return Mage::helper('checkout/cart')->getAddUrl($product, $additional);
        }
        $additional = array_merge(
            $additional,
            array('form_key' => $this->_getSingletonModel('core/session')->getFormKey())
        );
        if (!isset($additional['_escape'])) {
            $additional['_escape'] = true;
        }
        if (!isset($additional['_query'])) {
            $additional['_query'] = array();
        }
        $additional['_query']['options'] = 'cart';
        return $this->getProductUrl($product, $additional);
    }
    
    public function getProductUrl($product, $additional = array())
    {
        if ($this->hasProductUrl($product)) {
            if (!isset($additional['_escape'])) {
                $additional['_escape'] = true;
            }
            return $product->getUrlModel()->getUrl($product, $additional);
        }
        return '#';
    }
    
    protected function _getSingletonModel($className, $arguments = array())
    {
        return Mage::getSingleton($className, $arguments);
    }
    
    public function hasProductUrl($product)
    {
        if ($product->getVisibleInSiteVisibilities()) {
            return true;
        }
        return false;
    }
}