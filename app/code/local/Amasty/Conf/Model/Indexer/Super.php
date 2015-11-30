<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Conf
 */
class Amasty_Conf_Model_Indexer_Super extends Mage_Index_Model_Indexer_Abstract
{
    const   EVENT_MATCH_RESULT_KEY = 'amconf_match_result';
    public static $indexSuperData = array();
    /**
     * Retrieve Indexer name
     *
     * @return string
     */
    public function getName() {
        return Mage::helper('amconf')->__('Amasty Color Swatches Pro');
    }
    /**
     * Retrieve Indexer description
     *
     * @return string
     */
    public function getDescription() {
        return Mage::helper('amconf')->__('Data from category page');
    }
    /**
     * Register data required by process in event object
     *
     * @param Mage_Index_Model_Event $event
     */
    protected function _registerEvent(Mage_Index_Model_Event $event)
    {

    }

    public function matchEvent(Mage_Index_Model_Event $event)
    {
        return false;
    }
    /**
     * Process event
     *
     * @param Mage_Index_Model_Event $event
     */
    protected function _processEvent(Mage_Index_Model_Event $event)
    {

    }

    public function reindexAll()
    {
        if (!Mage::getStoreConfig('amconf/list/list_index'))
            return;

        return $this->doReindexAll();
    }

    protected function doReindexAll() {
        $imageSizeAtCategoryPageX  = Mage::getStoreConfig('amconf/list/main_image_list_size_x');
        $imageSizeAtCategoryPageY  = Mage::getStoreConfig('amconf/list/main_image_list_size_y');
        $collectionConfigurable = Mage::getResourceModel('catalog/product_collection')
            ->addAttributeToSelect('small_image')
            ->addAttributeToFilter('type_id', array('eq' => 'configurable'));
        foreach ($collectionConfigurable as $configurableProduct) {
            $smallImage = '';
            if(!('no_selection' == $configurableProduct->getSmallImage() || '' == $configurableProduct->getSmallImage())) {
				try{
                    $smallImage = (string)(Mage::helper('catalog/image')->init($configurableProduct, 'small_image')->resize($imageSizeAtCategoryPageX, $imageSizeAtCategoryPageY));
				}
				catch(Exception $exc){}
            }
            self::$indexSuperData[$configurableProduct->getId()]= array(
                'sku'             => $configurableProduct->getSku(),
                'small_image_url' => $smallImage,
                'simples'         => array()
            );

            $childProducts = Mage::getModel('catalog/product_type_configurable')->getUsedProducts(null, $configurableProduct);
            foreach($childProducts as $childProduct) {
                $childProduct = Mage::getModel('catalog/product')->load($childProduct->getId());
                self::$indexSuperData[$configurableProduct->getId()]['simples'][$childProduct->getId()] = array(
                    'sku'   => $childProduct->getSku(),
                );
                if(!('no_selection' == $childProduct->getSmallImage() || '' == $childProduct->getSmallImage())){
                    try{
                        self::$indexSuperData[$configurableProduct->getId()]['simples'][$childProduct->getId()]['small_image_url' ] =
                            (string)(Mage::helper('catalog/image')->init($childProduct, 'small_image')->resize($imageSizeAtCategoryPageX, $imageSizeAtCategoryPageY));
                    }
                    catch(Exception $exc){}
                }
            }
        }
        $cacheFilePath = Mage::getBaseDir('var') . DS . 'cache' . '/indexSuperData';
        file_put_contents($cacheFilePath, serialize(self::$indexSuperData));
        return true;
    }

    protected function loadIndexSuperData() {
        if(! isset(self::$indexSuperData) || count(self::$indexSuperData) < 1) {
            self::$indexSuperData = array();
            $cacheFilePath = Mage::getBaseDir('var') . DS . 'cache' . '/indexSuperData';
            if(file_exists($cacheFilePath)) {
                self::$indexSuperData = unserialize(file_get_contents($cacheFilePath));
            }
            else{
                $this->doReindexAll();
                if(file_exists($cacheFilePath)) {
                    self::$indexSuperData = unserialize(file_get_contents($cacheFilePath));
                }
            }
        }
    }

    public function getPersistedDataById($productId, $type) {
        $this->loadIndexSuperData();
        if($type == 'configurable') {
            return self::$indexSuperData[$productId];
        }
        else if ($type == 'simple') {
            foreach(self::$indexSuperData as $conf) {
                foreach($conf['simples'] as $key => $value) {
                    if($key == $productId) {
                        return $conf['simples'][$key];
                    }
                }
            }
        } else {
            return NULL;
        }
    } 
}