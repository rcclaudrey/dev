<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Conf
 */
class Amasty_Conf_Model_Catalog_Product_Type_Configurable_Price extends Mage_Catalog_Model_Product_Type_Configurable_Price
{
    /**
     * @param float $qty
     * @param Mage_Catalog_Model_Product $product
     * @return float
     */
    public function getFinalPrice($qty = null, $product)
    {
        if (!(Mage::helper('amconf')->getConfigUseSimplePrice() == 2 ||(Mage::helper('amconf')->getConfigUseSimplePrice() == 1 AND $product->getData('amconf_simple_price'))))
        {
            return parent::getFinalPrice($qty, $product);
        }
        
        if (is_null($qty) && !is_null($product->getCalculatedFinalPrice())) {
            return $product->getCalculatedFinalPrice();
        }
        
        if (version_compare(Mage::getVersion(), '1.7', '<'))
        {
            // based on 1.6.2.0 version
            
            // get configurable's own price, 
            // apply its tier and special prices, 
            // dispatch 'catalog_product_get_final_price'
            // at last apply its custom options price
            $finalPrice = Mage_Catalog_Model_Product_Type_Price::getFinalPrice($qty, $product);
            
            // skip native configurable price calculation
            // ...
            
            // get child simple product, calculate its price with tier and special prices applied
            // and that's the only we need, we ignore all above ))
            $finalPrice = $this->_calcAmConfigurablePrice($qty, $product, $finalPrice);
        }
        else 
        {
            // based on 1.7.0.2 version
            
            // get configurable's own price, 
            // apply its group, tier and special prices, 
            $basePrice = $this->getBasePrice($product, $qty);
            
            // dispatch 'catalog_product_get_final_price'
            $finalPrice = $basePrice;
            $product->setFinalPrice($finalPrice);
            Mage::dispatchEvent('catalog_product_get_final_price', array('product' => $product, 'qty' => $qty));
            $finalPrice = $product->getData('final_price');
            
            // skip native configurable price calculation
            // $finalPrice += $this->getTotalConfigurableItemsPrice($product, $finalPrice);
            
            // at last apply its custom options price
            $finalPrice += $this->_applyOptionsPrice($product, $qty, $basePrice) - $basePrice;
            
            // get child simple product, calculate its price with tier and special prices applied
            // and that's the only we need, we ignore all above ))
            $finalPrice = $this->_calcAmConfigurablePrice($qty, $product, $finalPrice);
        }
        
        $product->setFinalPrice($finalPrice);
        return max(0, $product->getData('final_price'));
    }
    
    public function _calcAmConfigurablePrice($qty, $product, $finalPrice)
    {
        $price = $finalPrice;

        if ($product->getCustomOption('simple_product'))
        {
            $subProductId = $product->getCustomOption('simple_product')->getValue();
            $subProduct = Mage::getModel('catalog/product')->load($subProductId);
        }
        else
        {
            $prodType = $product->getTypeInstance(true);
            /* @var $prodType Mage_Catalog_Model_Product_Type_Configurable */

            $selectedAttributes = array();
            if ($product->getCustomOption('attributes')) 
            {
                $selectedAttributes = unserialize($product->getCustomOption('attributes')->getValue());
            }

            $subProduct = $prodType->getProductByAttributes($selectedAttributes, $product);
        }
        /* @var $subProduct Mage_Catalog_Model_Product */
        
        if ($subProduct AND $subProduct->getId())
        {
            $price = $subProduct->getFinalPrice($qty);
        }
        
        return $price;
    }
    
}
