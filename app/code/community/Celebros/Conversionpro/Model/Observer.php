<?php
/**
 * Celebros Conversion Pro - Magento Extension
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish correct extension functionality.
 * If you wish to customize it, please contact Celebros.
 *
 * @category    Celebros
 * @package     Celebros_Conversionpro
 * @author      Shay Acrich (email: me@shayacrich.com)
 *
 */
class Celebros_Conversionpro_Model_Observer
{
    
    /**
     * Reset search engine if it is enabled for catalog navigation
     *
     * @param Varien_Event_Observer $observer
     */
    public function resetCurrentCatalogLayer(Varien_Event_Observer $observer)
    {
        //First reset the registry variable so that you won't get an error when trying to re-assign a value.
        Mage::unregister('current_layer');
        
        //If Conversionpro's disabler is activated, we'll run the default Magento search no matter what.
        $status = Mage::getSingleton('conversionpro/session')->getConversionproDisabler();
        if ($status && $status == true) {
            return;
        }
        
        //Now, check if conversionpro is enabled, and if the chosen category isn't blacklisted for nav2search.
        if (Mage::helper('conversionpro')->getIsEngineAvailable()) {
            Mage::register('current_layer', Mage::getSingleton('conversionpro/catalog_layer'));
            return;
        }
        
        //If Conversionpro didn't work out, check if Solr is available and if so then use it.
        $modules = array_keys((array)Mage::getConfig()->getNode('modules')->children());
        if (in_array('Enterprise_Search', $modules)) {
            if (Mage::helper('enterprise_search')->getIsEngineAvailableForNavigation()) {
                //We're not registering the solr search layer, because there's a separate enterprise observer for that.
                //Mage::register('current_layer', Mage::getSingleton('enterprise_search/catalog_layer'));
                return;
            }
        }
        //If all else fails, revert to the default search engine's layer.
        Mage::register('current_layer', Mage::getSingleton('catalog/layer'));
    }
    
    //@todo unify this with the previous function
    /**
     * Reset search engine if it is enabled for search navigation
     *
     * @param Varien_Event_Observer $observer
     */
    public function resetCurrentSearchLayer(Varien_Event_Observer $observer)
    {
        Mage::unregister('current_layer');
        
        //If Conversionpro's disabler is activated, we'll run the default Magento search no matter what.
        $status = Mage::getSingleton('conversionpro/session')->getConversionproDisabler();
        if ($status && $status == true) {
            return;
        }
        
        if (Mage::helper('conversionpro')->getIsEngineAvailable()) {
            Mage::register('current_layer', Mage::getSingleton('conversionpro/search_layer'));
        } else {
            $modules = array_keys((array)Mage::getConfig()->getNode('modules')->children());
            if (in_array('Enterprise_Search', $modules)) {
                if (Mage::helper('enterprise_search')->getIsEngineAvailableForNavigation()) {
                    //We're not registering the solr search layer, because there's a separate enterprise observer for that.
                    //Mage::register('current_layer', Mage::getSingleton('enterprise_search/search_layer'));
                } else {
                    Mage::register('current_layer', Mage::getSingleton('catalogsearch/layer'));
                }
            } else {
                Mage::register('current_layer', Mage::getSingleton('catalogsearch/layer'));
            }
        }
    }
    
    /**
     * Define integration mode and add handle
     *
     * @param Varien_Event_Observer $observer
     */
    public function intModeCheck(Varien_Event_Observer $observer)
    {
        if (Mage::helper('conversionpro')->isConversionproEnabled()) {
            $actions = array_keys((array)Mage::getConfig()->getNode('conversionpro/search_actions'));
            $nav2search_actions = array_keys((array)Mage::getConfig()->getNode('conversionpro/nav2search_actions'));
            $current_action = (string)$observer->getAction()->getFullActionName();
            $update = $observer->getEvent()->getLayout()->getUpdate();
            if ((count($actions) && in_array($current_action, $actions))
            || (count($nav2search_actions) && in_array($current_action, $nav2search_actions) && Mage::getStoreConfig('conversionpro/nav_to_search_settings/nav_to_search'))) {
                if (Mage::getStoreConfig('conversionpro/nav_to_search_settings/nav_to_search')
                && Mage::getStoreConfig('conversionpro/nav_to_search_settings/nav_to_search_enable_blacklist')) {
                    $current_category = Mage::registry('current_category');
                    if ($current_category) {
                        $blacklist = Mage::getStoreConfig('conversionpro/nav_to_search_settings/nav_to_search_blacklist');
                        if (in_array($current_category->getEntityId(), explode(',', $blacklist))) {
                            return;
                        }
                    }
                }
                
                $this->addSearchHandle($update);
                if (Mage::helper('conversionpro')->isHideContent()) {
                    $this->hideContent($update);
                }
            }
        }
        /*Zend_Debug::dump(Mage::app()->getLayout()->getUpdate()->getHandles()); exit;*/
    }
    
    public function hideContent($update)
    {
        $update->addHandle('celebros_hide_content');
    }
    
    public function addSearchHandle($update)
    {
        $update->removeHandle('catalogsearch_result_index');
        $update->addHandle('catalogsearch_include');
    }
    
    /**
     * Add js libs before magento's js
     *
     * @param Varien_Event_Observer $observer
     */
    public function addLibz(Varien_Event_Observer $observer)
    {
        $js_head = Mage::app()->getLayout()->getBlock('js_head');
        $head   = Mage::app()->getLayout()->getBlock('head');
        if ($js_head instanceof Celebros_Conversionpro_Block_Js_Head 
        && is_object($head)) {
            $head   = Mage::app()->getLayout()->getBlock('head');
            $data   = $head->getData();
            $tmp    = $data['items'];
            $data['items'] = '';
            $head->setData($data);
            $urlz   = $js_head->getJsUrlz();
            if (!empty($urlz)) {
                foreach ($urlz as $url) {
                    $head->addJs($url);
                }
            }
            
            $data = $head->getData();
            if (!$data['items']) {
                $data['items'] = array();
            }
            
            $data['items'] = array_merge((array)$data['items'], (array)$tmp);
            $head->setData($data);
        }
    }
    
    /**
     * Add Celebros Data to head
     *
     * @param Varien_Event_Observer $observer
     */
    public function addHostData(Varien_Event_Observer $observer)
    {
        $block = $observer->getEvent()->getBlock();
        if ($block instanceof Mage_Page_Block_Html_Head) {
            $transport = $observer->getEvent()->getTransport();
            $html = $transport->getHtml();
            $result = "<script type=\"text/javascript\">var celebros_sitekey = '" . Mage::helper('conversionpro')->getSiteKey() . "'</script>";
            $transport->setHtml($result . $html);
        }
    }
    
    public function filterQueryParam(Varien_Event_Observer $observer)
    {
        $redirect = FALSE;
        $params = Mage::app()->getRequest()->getParams();
        foreach ($params as $key=>$param) {
            if ($key == Mage::helper('catalogsearch')->getQueryParamName()) {
                $new_param = strip_tags($params[$key]);
                if ($new_param != $params[$key]) {
                    $redirect = TRUE;
                    $params[$key] = $new_param;
                }
            }
        }
        
        if ($redirect) {
            $sett['_secure'] = TRUE;
            $sett['_query'] = $params;
            Mage::app()->getResponse()->setRedirect(Mage::getUrl('catalogsearch/result', $sett));
        }
    }
 
}