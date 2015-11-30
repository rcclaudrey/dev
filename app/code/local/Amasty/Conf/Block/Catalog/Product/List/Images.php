<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Conf
 */
class Amasty_Conf_Block_Catalog_Product_List_Images extends Mage_Core_Block_Template
{
    protected $product;
    protected $block;
    protected $html;
    protected $productId;
    
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('amasty/amconf/options.phtml');
        $this->block = Mage::app()->getLayout()->createBlock('amconf/catalog_product_view_type_configurable', 'amconf.catalog_product_view_type_configurable');
    }

    public function getHtml() {
        $this->block->setProduct($this->product);
        $this->block->setNameInLayout('product.info.options.configurable');
        $this->block->getJsonConfig();
        return $this->block->_afterToHtml($this->html,1);
    } 
    
    public function getAttributes() {
        $data = Zend_Json::decode($this->block->getJsonConfig());
        $attributes = $data['attributes'];
        $keys = array();
        foreach($attributes as $key=>$attribute){
            $keys[] = $key;
        }
        return Zend_Json::encode($keys);
    } 
    
    public function show($productId) {
        $this->productId = $productId;
        $this->product = Mage::getModel('catalog/product')->load($productId); 
        $blockOptions = Mage::app()->getLayout()->createBlock('catalog/product_view_type_configurable', 'catalog.product_view_type_configurable',array('template'=>"amasty/amconf/configurable.phtml"));
        $blockOptions->setProduct($this->product);
        $this->html = $blockOptions->toHtml(); 
        return $this->toHtml();
    }
    
    public function getJsonConfig()
    {
        $config = array();
        $_request = Mage::getSingleton('tax/calculation')->getRateRequest(false, false, false);
        $product = $this->product; 
        $_request->setProductClassId($product->getTaxClassId());
        $defaultTax = Mage::getSingleton('tax/calculation')->getRate($_request);

        $_request = Mage::getSingleton('tax/calculation')->getRateRequest();
        $_request->setProductClassId($product->getTaxClassId());
        $currentTax = Mage::getSingleton('tax/calculation')->getRate($_request);

        $_regularPrice = $product->getPrice();
        $_finalPrice = $product->getFinalPrice();
        $_priceInclTax = Mage::helper('tax')->getPrice($product, $_finalPrice, true);
        $_priceExclTax = Mage::helper('tax')->getPrice($product, $_finalPrice);
        $_tierPrices = array();
        $_tierPricesInclTax = array();
        foreach ($product->getTierPrice() as $tierPrice) {
            $_tierPrices[] = Mage::helper('core')->currency($tierPrice['website_price'], false, false);
            $_tierPricesInclTax[] = Mage::helper('core')->currency(
                Mage::helper('tax')->getPrice($product, (int)$tierPrice['website_price'], true),
                false, false);
        }
        $config = array(
            'productId'           => $product->getId(),
            'priceFormat'         => Mage::app()->getLocale()->getJsPriceFormat(),
            'includeTax'          => Mage::helper('tax')->priceIncludesTax() ? 'true' : 'false',
            'showIncludeTax'      => Mage::helper('tax')->displayPriceIncludingTax(),
            'showBothPrices'      => Mage::helper('tax')->displayBothPrices(),
            'productPrice'        => Mage::helper('core')->currency($_finalPrice, false, false),
            'productOldPrice'     => Mage::helper('core')->currency($_regularPrice, false, false),
            'priceInclTax'        => Mage::helper('core')->currency($_priceInclTax, false, false),
            'priceExclTax'        => Mage::helper('core')->currency($_priceExclTax, false, false),
            'skipCalculate'       => ($_priceExclTax != $_priceInclTax ? 0 : 1),
            'defaultTax'          => $defaultTax,
            'currentTax'          => $currentTax,
            'idSuffix'            => '_clone',
            'oldPlusDisposition'  => 0,
            'plusDisposition'     => 0,
            'plusDispositionTax'  => 0,
            'oldMinusDisposition' => 0,
            'minusDisposition'    => 0,
            'tierPrices'          => $_tierPrices,
            'tierPricesInclTax'   => $_tierPricesInclTax,
        );

        $responseObject = new Varien_Object();
        Mage::dispatchEvent('catalog_product_view_config', array('response_object'=>$responseObject));
        if (is_array($responseObject->getAdditionalOptions())) {
            foreach ($responseObject->getAdditionalOptions() as $option=>$value) {
                $config[$option] = $value;
            }
        }

        return Mage::helper('core')->jsonEncode($config);
    }
}