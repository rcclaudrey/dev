<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Conf
 */
class Amasty_Conf_Block_Catalog_Product_Price extends Mage_Catalog_Block_Product_Price
{
    private $_confProduct = null;

    public function _toHtml() {
        if (Mage::getStoreConfig('amconf/general/display_price') && $this->getTemplate() == 'catalog/product/price.phtml') {
            $product = $this->getProduct();
            $product = $this->_confProduct;
            if (is_object($product) && $product->isConfigurable()) {
                $priceHtml = parent::_toHtml();

                /* add label before*/
                $priceTag = '<div class="price-box">';
                $labelHtml = $priceTag .
                            '<span class="label configurable-price-from">' .
                                 $this->__('Price From:') . '
                             </span>';
                $priceHtml = str_replace($priceTag, $labelHtml, $priceHtml);

                /*save configurable id*/
                preg_match_all("/price-([0-9]+)/", $priceHtml, $res);
                if($res[0]){
                    return str_replace($res[0][0], "price-" . $product->getId(), $priceHtml);

                }
            }
        }
        return parent::_toHtml();
    }

    public function getProduct() {
        $product =  parent::getProduct();
        $this->_confProduct = $product;
        if (Mage::getStoreConfig('amconf/general/display_price')) {
            $product = Mage::helper('amconf')->getSimpleProductWithMinPrice($product);
        }

        return $product;
    }
}

