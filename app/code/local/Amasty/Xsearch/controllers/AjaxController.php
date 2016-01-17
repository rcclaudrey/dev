<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Xsearch
 */
require_once Mage::getModuleDir('controllers', 'Mage_Checkout').DS.'CartController.php';

class Amasty_Xsearch_AjaxController extends Mage_Core_Controller_Front_Action
{
    
    protected function _getPriceHTML($_product){
        $layout = Mage::getSingleton('core/layout');
        
        $catalogProduct = null;
        
        if ($_product->getTypeId() == 'bundle'){
            $catalogProduct = $layout->createBlock('bundle/catalog_product_price');
            $catalogProduct->setData('product', $_product);
            $catalogProduct->setTemplate('bundle/catalog/product/price.phtml');
        } else {
            $catalogProduct = $layout->createBlock('catalog/product_price');
            $catalogProduct->setData('product', $_product);
            $catalogProduct->setTemplate('catalog/product/price.phtml');
        }
        return $catalogProduct->toHTML();
    }
    
    protected function _getReviewHTML($_product){
        $layout = Mage::getSingleton('core/layout');
        $review = $layout->createBlock('review/helper');

        $review->setTemplate('review/helper/summary.phtml');


        return $review->getSummaryHtml($_product, 'short', false);
    }
    
    public function indexAction()
    {
        header('Access-Control-Allow-Origin: *');  
        
        $hlr = Mage::helper("amxsearch");
        
        $result = array(
            'items' => array(),
            'bottomHtml' => ''
        );
        
        $query = Mage::helper('catalogsearch')->getQuery();
        /* @var $query Mage_CatalogSearch_Model_Query */

        $query->setStoreId(Mage::app()->getStore()->getId());

        if ($query->getQueryText() != '') {
            if (Mage::helper('catalogsearch')->isMinQueryLength()) {
                $query->setId(0)
                    ->setIsActive(1)
                    ->setIsProcessed(1);
            }
            else {
                if ($query->getId()) {
                    $query->setPopularity($query->getPopularity()+1);
                }
                else {
                    $query->setPopularity(1);
                }

                if ($query->getRedirect()){
                    $query->save();
                    $this->getResponse()->setRedirect($query->getRedirect());
                    return;
                }
                else {
                    $query->prepare();
                }
            }
        }
        
        
        $limit = Mage::getStoreConfig('amxsearch/autocomplete/products_limit');
        $nameLength = Mage::getStoreConfig('amxsearch/autocomplete/name_length');
        $descLength = Mage::getStoreConfig('amxsearch/autocomplete/desc_length');
        $showReviews = Mage::getStoreConfig('amxsearch/autocomplete/reviews') == 1;
        
        $_resultCollection = Mage::getSingleton('catalogsearch/layer')->getProductCollection();
        $_resultCollection->getSelect()->order('relevance desc');

        if ($_resultCollection->getSize() > $limit){
            
            $moreResults = Mage::getUrl('catalogsearch/result/index', array(
                'q' => $_GET['q']
            ));

            $result['bottomHtml'] = '<div class="more_results"><a href="'.$moreResults.'"> ' . ($hlr->__("More results")). ' </a></div>';
        }
        
        
        $_resultCollection->getSelect()->limit($limit);
        
        $catalogOutputHelper = Mage::helper('catalog/output');
        $catalogImageHelper = Mage::helper('catalog/image');
        $amxsearchHelper = Mage::helper('amxsearch/data');
        
//print $_resultCollection->getSelect();
        foreach($_resultCollection as $_product){
            
            if ($_product->getTypeId() == 'bundle'){
                $_product->setFinalPrice($_product->getMinPrice());
            }
            
            $desc = $catalogOutputHelper->productAttribute($_product, $_product->getShortDescription(), 'short_description');
            $result['items'][] = array(
                'price' => $this->_getPriceHTML($_product),
                'reviews' => $showReviews ? $this->_getReviewHTML($_product) : '',
                'description' => $amxsearchHelper->substr($catalogOutputHelper->stripTags($desc, null, true), $descLength),
                'name' => $amxsearchHelper->substr($catalogOutputHelper->stripTags($_product->getName(), null, true), $nameLength),
                'url' => $_product->getProductUrl(),
                'add_to_cart' => $this->_getAdd2CartHtml($_product),
                'image' => $catalogImageHelper->init($_product, 'small_image')->resize(135)->__toString(),
            );
            
            
        
//           $_product->getProductUrl()
//                   $this->helper('catalog/image')->init($_product, 'small_image')->resize(135);
        }
        
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }
    
    protected function _getAdd2CartHtml($_product){
        $hlr = Mage::helper("amxsearch");
        
        $html = '';
        
        if (Mage::getStoreConfig('amxsearch/autocomplete/add2cart')) {
            if($_product->isSaleable()) {
                $url = $hlr->getAddToCartUrl($_product, array(
                    'return_url' => urlencode(Mage::getUrl('checkout/cart'))
                ));

                $html = '<span class="add2cart"><button type="button" title="' . $hlr->__('Add to Cart') . '" class="button btn-cart" onclick="setLocation(\'' . $url . '\'); return false;"><span><span>' . $hlr->__('Add to Cart') . '</span></span></button></span>';
            } else {
                $html = '<span class="add2cart availability out-of-stock"><span>' . $hlr->__('Out of stock') . '</span></span>';
            }
        }
        return $html;
    }
    
}
