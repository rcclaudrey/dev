<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Conf
 */
class Amasty_Conf_Block_Catalog_Product_View_Type_ConfigurablelIndex extends Amasty_Conf_Block_Catalog_Product_View_Type_Configurablel
{
    protected function _toHtml()
    {
        $html = parent::_parentToHtml();

        if ('product.info.options.configurable' == $this->getNameInLayout())
        {
            $html = str_replace('super-attribute-select', 'no-display super-attribute-select', $html);

            $_product = $this->getProduct();
            $id = $_product->getEntityId();
            $indexModel = Mage::getModel('amconf/indexer_super');
            if($indexModel) {
                $indexData = $indexModel->getPersistedDataById($id, 'configurable');
            }
            $_useSimplePrice =  (Mage::helper('amconf')->getConfigUseSimplePrice() == 2
                || (Mage::helper('amconf')->getConfigUseSimplePrice() == 1 AND $_product->getData('amconf_simple_price')))
                ? true : false;

            $parentImage = $indexData['small_image_url'];

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
                            $confData[$strKey]['small_image'] = isset($indexData['simples'][$simple->getId()]['small_image_url']) ? $indexData['simples'][$simple->getId()]['small_image_url'] : '';
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
                            $confData[$strKey]['small_image']  = $parentImage;
                            $confData[$strKey]['parent_image'] = $parentImage;
                        }

                        $confData[$strKey]['not_is_in_stock'] = !$simple->isSaleable();

                        // the html blocks are required for product view page
                        if ($_useSimplePrice)
                        {
                            $tierPriceHtml = $this->getTierPriceHtml($simple);
                            $confData[$strKey]['price_html'] = $this->getPriceHtml($simple) . $tierPriceHtml;
                            //$confData[$strKey]['price_clone_html'] = $this->getPriceHtml($simple, false, '_clone') . $tierPriceHtml;
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
}