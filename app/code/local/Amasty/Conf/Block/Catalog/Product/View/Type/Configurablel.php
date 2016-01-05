<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Conf
 */
class Amasty_Conf_Block_Catalog_Product_View_Type_Configurablel extends Mage_Catalog_Block_Product_View_Type_Configurable
{
    protected $_currentAttributes;
    protected $_jsonConfig;

    protected function _construct()
    {
        parent::_construct();

        if (version_compare(Mage::getVersion(), '1.9', '>=')){
            $productTag = 'catalog_product_' . $this->getProduct()->getId();

            $this->addData(array('cache_lifetime' => false));
            $this->addCacheTag(array(
                Mage_Core_Model_Store::CACHE_TAG,
                Mage_Core_Block_Abstract::CACHE_GROUP,
                $productTag
            ));
        }
    }

    /**
     * Get cache key informative items
     *
     * @return array
     */
    public function getCacheKeyInfo()
    {
        return array(
            'AMASTY_CONF_CONFIGURABLE_CATEGORY',
            Mage::app()->getStore()->getId(),
            (int)Mage::app()->getStore()->isCurrentlySecure(),
            Mage::getDesign()->getPackageName(),
            Mage::getDesign()->getTheme('template'),
            Mage::getSingleton('customer/session')->isLoggedIn(),
            Mage::getSingleton('customer/session')->getCustomerGroupId(),
            $this->getProduct()->getId()
        );
    }

    protected function _parentToHtml()
    {
        return parent::_toHtml();
    }

    protected function _toHtml()
    {
        $html = parent::_toHtml();

        if ('product.info.options.configurable' == $this->getNameInLayout())
        {
            $html = str_replace('super-attribute-select', 'no-display super-attribute-select', $html);

            $_product = $this->getProduct();
            $_useSimplePrice =  (Mage::helper('amconf')->getConfigUseSimplePrice() == 2
                                || (Mage::helper('amconf')->getConfigUseSimplePrice() == 1 AND $_product->getData('amconf_simple_price')))
                            ? true : false;

            $id = $_product->getEntityId();

            $imageSizeAtCategoryPageX = Mage::getStoreConfig('amconf/list/main_image_list_size_x');
            $imageSizeAtCategoryPageY = Mage::getStoreConfig('amconf/list/main_image_list_size_y');
            $parentImage = (string)($this->helper('catalog/image')->init($_product, 'small_image')->resize($imageSizeAtCategoryPageX, $imageSizeAtCategoryPageY));
            $productUrl = $_product->getProductUrl();
            $productUrl = substr($productUrl, strrpos($productUrl, "/"));

            $confData = array(
                'textNotAvailable'      => $this->__('Choose previous option please...'),
                'useSimplePrice'        => intval($_useSimplePrice),
                'url'                   => $productUrl,
                'onclick'				=> Mage::helper('checkout/cart')->getAddUrl($_product)
            );

            $simpleProducts = $this->getProduct()->getTypeInstance(true)->getUsedProducts(null, $_product);
            if ($this->_currentAttributes)
            {
                $this->_currentAttributes = array_unique($this->_currentAttributes);
                foreach ($simpleProducts as $simple)
                {
                    /* @var $simple Mage_Catalog_Model_Product */
                    $key = array();
                    foreach ($this->_currentAttributes as $attributeCode)
                    {
                        $key[] = $simple->getData($attributeCode);
                    }

                    if ($key)
                    {
                        $strKey = implode(',', $key);
                        $confData[$strKey] = array();

                        if(!('no_selection' == $simple->getSmallImage() || '' == $simple->getSmallImage())){
				            $confData[$strKey]['small_image'] = (string)($this->helper('catalog/image')->init($simple, 'small_image')->resize($imageSizeAtCategoryPageX, $imageSizeAtCategoryPageY));
                            $confData[$strKey]['parent_image'] = $parentImage;
                            if(Mage::getStoreConfig('amconf/general/oneselect_reload')) {
                                $k = $strKey;
                                if(strpos($strKey, ',') > 0){
                                    $k = substr($strKey, 0, strpos($strKey, ','));
                                }
                                if(!(array_key_exists($k, $confData) && array_key_exists('small_image', $confData[$k]))){
                                    $confData[$k]['small_image'] = $confData[$strKey]['small_image'];
                                    $confData[$k]['parent_image'] = $confData[$strKey]['parent_image'];
                                }
                            }
                            else{
                               //for only first
                            }
                        }
                        else{
                           $confData[$strKey]['small_image'] = $parentImage;
                           $confData[$strKey]['parent_image'] = $parentImage;
                        }

                        $confData[$strKey]['not_is_in_stock'] = !$simple->isSaleable();

                        // the html blocks are required for product view page
                        if ($_useSimplePrice)
                        {
                            $tierPriceHtml = $this->getTierPriceHtml($simple);
                            $confData[$strKey]['price_html'] = $this->getPriceHtml($simple) . $tierPriceHtml;
                        }
                        //for >3
                        if(Mage::getStoreConfig('amconf/general/oneselect_reload')){
                            $pos = strpos($strKey, ",");
                            if($pos){
                                $pos = strpos($strKey, ",", $pos+1);
                                if($pos){
                                    $newKey = substr($strKey, 0, $pos);
                                    $confData[$newKey] =  $confData[$strKey];
                                }
                            }

                        }

                    }
                }

                $html = '<script type="text/javascript"> 
                              confData['. $id .'] = new AmConfigurableData(' . Zend_Json::encode($confData) . ');
                              amRequaredField = "' .  $this->__('&uarr;  This is a required field.') . '";
                        </script>'
                    . $html;
            }
        }

        return $html;
    }

    protected function getImagesFromProductsAttributes(){
        $collection = Mage::getModel('amconf/product_attribute')->getCollection();
        $collection->addFieldToFilter('use_image_from_product', 1);

        $collection->getSelect()->join( array(
            'prodcut_super_attr' => $collection->getTable('catalog/product_super_attribute')),
                'main_table.product_super_attribute_id = prodcut_super_attr.product_super_attribute_id',
                array('prodcut_super_attr.attribute_id')
            );

        $collection->addFieldToFilter('prodcut_super_attr.product_id', $this->getProduct()->getEntityId());

        $attributes = $collection->getItems();
        $ret = array();

        foreach($attributes as $attribute){
            $ret[] = $attribute->getAttributeId();
        }

        return $ret;
    }

    public function getAllowProducts()
    {
        if (!$this->hasAllowProducts()) {
            $products = array();
            $allProducts = $this->getProduct()->getTypeInstance(true)
                ->getUsedProducts(null, $this->getProduct());
            foreach ($allProducts as $product) {
                /**
                * Should show all products (if setting set to Yes), but not allow "out of stock" to be added to cart
                */
                 if ($product->isSaleable() || Mage::getStoreConfig('amconf/general/out_of_stock') ) {
                    if ($product->getStatus() != Mage_Catalog_Model_Product_Status::STATUS_DISABLED)
                    {
                        if (in_array(Mage::app()->getStore()->getWebsiteId(), $product->getWebsiteIds())) {
                            $products[] = $product;
                        }
                    }
                }
            }
            $this->setAllowProducts($products);
        }
        return $this->getData('allow_products');
    }

    public function getJsonConfig()
    {
        $jsonConfig = parent::getJsonConfig();
        $config = Zend_Json::decode($jsonConfig);
        $productImagesAttributes = $this->getImagesFromProductsAttributes();

        foreach ($config['attributes'] as $attributeId => $attribute)
        {
            $attr = Mage::getModel('amconf/attribute')->load($attributeId, 'attribute_id');
            $this->_currentAttributes[] = $attribute['code'];

            if ($attr->getUseImage())
            {
                $config['attributes'][$attributeId]['use_image'] = 1;
                $config['attributes'][$attributeId]['config'] = $attr->getData();

                $smWidth = $attr->getCatSmallWidth() != "0"? $attr->getCatSmallWidth(): 25;
                $smHeight = $attr->getCatSmallHeight()!= "0"? $attr->getCatSmallHeight(): 25;
                $bigWidth = $attr->getCatBigWidth()!= "0"? $attr->getCatBigWidth(): 50;
                $bigHeight = $attr->getCatBigHeight()!= "0"? $attr->getCatBigHeight(): 50;

                foreach ($attribute['options'] as $i => $option)
                {
                    if (in_array($attributeId, $productImagesAttributes)){

                        foreach($option['products'] as $product_id){

                            $product = Mage::getModel('catalog/product')->load($product_id);
                            $config['attributes'][$attributeId]['options'][$i]['image'] =
                                (string)Mage::helper('catalog/image')->init($product, 'image')->resize($smWidth, $smHeight);
                            if(in_array($attr->getCatUseTooltip(), array("2", "3")))
                                $config['attributes'][$attributeId]['options'][$i]['bigimage'] =
                                    (string)Mage::helper('catalog/image')->init($product, 'image')->resize($bigWidth, $bigHeight);
                            break;
                        }
                    }
                    else {
                        $imgUrl = Mage::helper('amconf')->getImageUrl($option['id'], $smWidth, $smHeight);
                        $tooltipUrl = Mage::helper('amconf')->getImageUrl($option['id'], $bigWidth, $bigHeight);
                        if($imgUrl == ""){
                            $imgUrl = Mage::helper('amconf')->getPlaceholderUrl($attributeId, $smWidth, $smHeight);
                            $tooltipUrl = Mage::helper('amconf')->getPlaceholderUrl($attributeId, $bigWidth, $bigHeight);
                        }
                        $config['attributes'][$attributeId]['options'][$i]['image'] = $imgUrl;
                        if(in_array($attr->getCatUseTooltip(), array("2", "3")))
                            $config['attributes'][$attributeId]['options'][$i]['bigimage'] = $tooltipUrl;

                        $swatchModel = Mage::getModel('amconf/swatch')->load($option['id']);
                        $config['attributes'][$attributeId]['options'][$i]['color'] = $swatchModel->getColor();
                    }
                }
            }

        }
        $this->_jsonConfig = $config;
        return Zend_Json::encode($config);
    }

    public function getAddToCartUrl($product, $additional = array())
    {
        if ($this->hasCustomAddToCartUrl()) {
            return $this->getCustomAddToCartUrl();
        }
        if ($this->getRequest()->getParam('wishlist_next')){
            $additional['wishlist_next'] = 1;
        }
        $addUrlKey = Mage_Core_Controller_Front_Action::PARAM_NAME_URL_ENCODED;
        $addUrlValue = Mage::getUrl('*/*/*', array('_use_rewrite' => true, '_current' => true));
        $additional[$addUrlKey] = Mage::helper('core')->urlEncode($addUrlValue);
        return $this->helper('checkout/cart')->getAddUrl($product, $additional);
    }

    public function getPriceJsonConfig()
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

    public function isSalable($product = null){
         $salable = parent::isSalable($product);

        if ($salable !== false) {
            $salable = false;
            if (!is_null($product)) {
                $this->setStoreFilter($product->getStoreId(), $product);
            }

            if (!Mage::app()->getStore()->isAdmin() && $product) {
                $collection = $this->getUsedProductCollection($product)
                    ->addAttributeToFilter('status', Mage_Catalog_Model_Product_Status::STATUS_ENABLED)
                    ->setPageSize(1)
                    ;
                if ($collection->getFirstItem()->getId()) {
                    $salable = true;
                }
            } else {
                foreach ($this->getUsedProducts(null, $product) as $child) {
                    if ($child->isSalable()) {
                        $salable = true;
                        break;
                    }
                }
            }
        }

        return $salable;
    }
}