<?php

class SMDesign_SMDZoom_Block_Product_View_Media extends Mage_Catalog_Block_Product_View_Media {

    function _construct() {
        parent::_construct();

        $modules = Mage::getConfig()->getNode('modules')->children();
        $modulesArray = (array) $modules;
        if (!isset($modulesArray['SMDesign_Colorswatch']) || (isset($modulesArray['SMDesign_Colorswatch']) && $modulesArray['SMDesign_Colorswatch']->active == false)) {
            return $this; // Colorswatch module is not available.
        }

        $id = (int) Mage::app()->getRequest()->getParam('id');
        $inCartProduct = null;
        $requestByCart = array();
        if ($id) {
            $quoteItem = Mage::getSingleton('checkout/cart')->getQuote()->getItemById($id);
            if ($quoteItem) {
                $selectedOption = $quoteItem->getOptionByCode('simple_product');
                $inCartProduct = Mage::getModel('catalog/product')->load($selectedOption['product_id']);
                if ($inCartProduct->getImage()) {
                    $inCartProduct->setData('enable_zoom_plugin', 1);
                    $this->setProduct($inCartProduct);
                }
            }
        }

        if (Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE == $this->getProduct()->getTypeId()) {

            $usedAttributes = $this->getProduct()->getTypeInstance(true)->getUsedProductAttributes($this->getProduct());

            $productData = array();
            foreach ($usedAttributes as $attribute) {
                if (Mage::getModel('colorswatch/attribute_settings')->getConfig($attribute->getId(), 'allow_attribute_to_change_main_image')) {
                    $optionValue = Mage::app()->getRequest()->getParam($attribute->getAttributeCode(), ($inCartProduct ? $inCartProduct->getData($attribute->getAttributeCode()) : -1));
//                    if (-1 != $optionValue) {

                        foreach ($this->getProduct()->getTypeInstance(true)->getUsedProducts(null, $this->getProduct()) as $simpleProduct) {
                            if ($simpleProduct->isSaleable() && $simpleProduct->getData($attribute->getAttributeCode()) == $optionValue) {

                                $simpleProduct->load();
                                if (count($simpleProduct->getMediaGalleryImages()) > 0 && $simpleProduct->getImage()) {
                                    $simpleProduct->setData('enable_zoom_plugin', 1);
                                    $products[] = $simpleProduct;

                                    // unset produt without assingend secound attribute
                                    foreach ($products as $key => $val) {
                                        if ($val->getData($attribute->getAttributeCode()) != $optionValue) {
                                            unset($products[$key]);
                                        }
                                    }
                                }
                            }
                        }
//                    }
                }
            }

            if (isset($products) && is_array($products) && count($products) > 0) {
                $this->setProduct($products[0]);
            }
        }
    }

    public function getGalleryUrl($image = null) {
        $pid = Mage::getModel('catalog/session')->getCurrentSimpleProductId();
        $params = array('id' => ($pid ? $pid : $this->getProduct()->getId()));

        if ($image) {
            $params['image'] = $image->getValueId();
            return $this->getUrl('*/*/gallery', $params);
        }
        return $this->getUrl('*/*/gallery', $params);
    }

    public function getZoomConfig() {
        $zoomConfig = array();
        $zoomConfig['image_width'] = Mage::getStoreConfig('smdesign_smdzoom/zoom/image_width');
        $zoomConfig['image_height'] = Mage::getStoreConfig('smdesign_smdzoom/zoom/image_height');
        $zoomConfig['thumbnail_width'] = Mage::getStoreConfig('smdesign_smdzoom/zoom/thumbnail_width');
        $zoomConfig['thumbnail_height'] = Mage::getStoreConfig('smdesign_smdzoom/zoom/thumbnail_height');
        $zoomConfig['wrapper_width'] = Mage::getStoreConfig('smdesign_smdzoom/zoom/wrapper_width');
        $zoomConfig['wrapper_height'] = Mage::getStoreConfig('smdesign_smdzoom/zoom/wrapper_height');
        $zoomConfig['wrapper_offset_left'] = Mage::getStoreConfig('smdesign_smdzoom/zoom/wrapper_offset_left');
        $zoomConfig['wrapper_offset_top'] = Mage::getStoreConfig('smdesign_smdzoom/zoom/wrapper_offset_top');
        $zoomConfig['zoom_type'] = Mage::getStoreConfig('smdesign_smdzoom/zoom/zoom_type');
        $zoomConfig['zoom_ratio'] = intval(Mage::getStoreConfig('smdesign_smdzoom/zoom/zoom_ratio'));
        $zoomConfig['show_zoom_effect'] = Mage::getStoreConfig('smdesign_smdzoom/zoom/show_zoom_effect');
        $zoomConfig['hide_zoom_effect'] = Mage::getStoreConfig('smdesign_smdzoom/zoom/hide_zoom_effect');
        $zoomConfig['show_info_error'] = Mage::getStoreConfig('smdesign_smdzoom/zoom/show_info_error');
        $zoomConfig['more_view'] = Mage::getStoreConfig('smdesign_smdzoom/zoom/more_view_change_main_image');
        $zoomConfig['show_preloader'] = Mage::getStoreConfig('smdesign_smdzoom/zoom/show_preloader');

        if ($zoomConfig['zoom_ratio'] == "" || $zoomConfig['zoom_ratio'] == 0 || $zoomConfig['zoom_ratio'] == 1) {
            $zoomConfig['zoom_ratio'] = 2;
        }

        switch ($zoomConfig['zoom_type']) {
            default:
            case 0:
                /* outside */
                $ratioModifierWidth = 0;
                $ratioModifierHeight = 0;
                if ($zoomConfig['image_width'] * $zoomConfig['zoom_ratio'] <= $zoomConfig['wrapper_width']) {
                    $ratioModifierWidth = intval($zoomConfig['wrapper_width'] / ($zoomConfig['image_width'] * $zoomConfig['zoom_ratio']));
                }
                if ($zoomConfig['image_height'] * $zoomConfig['zoom_ratio'] <= $zoomConfig['wrapper_height']) {
                    $ratioModifierHeight = intval($zoomConfig['wrapper_height'] / ($zoomConfig['image_height'] * $zoomConfig['zoom_ratio']));
                }
                $zoomConfig['zoom_ratio'] = $zoomConfig['zoom_ratio'] + max($ratioModifierWidth, $ratioModifierHeight);
                break;
            case 1:
                /* inside */
                $zoomConfig['show_zoom_effect'] = "none";
                $zoomConfig['hide_zoom_effect'] = "none";
                $ratioModifierWidth = 0;
                $ratioModifierHeight = 0;

                if ($zoomConfig['image_width'] * $zoomConfig['zoom_ratio'] <= $zoomConfig['wrapper_width']) {
                    $ratioModifierWidth = intval($zoomConfig['wrapper_width'] / ($zoomConfig['image_width'] * $zoomConfig['zoom_ratio']));
                }
                if ($zoomConfig['image_height'] * $zoomConfig['zoom_ratio'] <= $zoomConfig['wrapper_height']) {
                    $ratioModifierHeight = intval($zoomConfig['wrapper_height'] / ($zoomConfig['image_height'] * $zoomConfig['zoom_ratio']));
                }
                $zoomConfig['zoom_ratio'] = $zoomConfig['zoom_ratio'] + max($ratioModifierWidth, $ratioModifierHeight);
                break;
            case 2:
                /* full */
                $zoomConfig['show_zoom_effect'] = "none";
                $zoomConfig['hide_zoom_effect'] = "none";
                $zoomConfig['wrapper_offset_left'] = 0;
                $zoomConfig['wrapper_offset_top'] = 0;
                $zoomConfig['wrapper_width'] = $zoomConfig['image_width'];
                $zoomConfig['wrapper_height'] = $zoomConfig['image_height'];
                break;
        }

        $zoomConfig['upscale_image_width'] = $zoomConfig['zoom_ratio'] * $zoomConfig['image_width'];
        $zoomConfig['upscale_image_height'] = $zoomConfig['zoom_ratio'] * $zoomConfig['image_height'];

        return $zoomConfig;
    }

    function changeTemplate($template) {
        if ($this->getProduct()->getData('enable_zoom_plugin') == 1 && !defined('SMD_LICENSE_ERROR')) {
            $this->setTemplate($template);
        }
    }

}
