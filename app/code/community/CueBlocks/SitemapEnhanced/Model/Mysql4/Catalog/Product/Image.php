<?php

/**
 * Description of SitemapEnhanced
 * @package   CueBlocks_SitemapEnhanced
 * @company   CueBlocks - http://www.cueblocks.com/
 
 */
class CueBlocks_SitemapEnhanced_Model_Mysql4_Catalog_Product_Image extends Mage_Core_Model_Mysql4_Abstract
{

    /**
     * Collection Zend Db select
     *
     * @var Zend_Db_Select
     */
    protected $_select;

    /**
     * Attribute cache
     *
     * @var array
     */
    protected $_attributesCache = array();

    /**
     * Init resource model (catalog/category)
     *
     */
    protected function _construct()
    {
        $this->_init('catalog/product_attribute_media_gallery', 'value_id');
    }

    /**
     * Get Image for products
     *
     * TODO: improve the join query
     *
     * @param string $storeId
     * @param string $prodId
     *
     * @return array
     */
    public function getCollection($storeId, $prodId)
    {

        $conditions_store = array(
            'e.value_id=e_st_attr.value_id',
            $this->_getWriteAdapter()->quoteInto('e_st_attr.store_id=?', $storeId)
        );

        $conditions_def = array(
            'e.value_id=e_def_attr.value_id',
            $this->_getWriteAdapter()->quoteInto('e_def_attr.store_id=?', 0)
        );

        $this->_select = $this->_getWriteAdapter()->select()
            ->from(array('e' => $this->getMainTable()), array('path' => 'e.value'))
            ->where('e.entity_id =?', $prodId);

        // Adding info to check if the image has been disabled
        // We need both value, this store and main store
        // join left as the value for specific store could not be in DB
        $this->_select = $this->_select
            // disabled value on default scope
            ->join(
                array('e_def_attr' => $this->getTable('catalog/product_attribute_media_gallery_value')),
                join(' AND ', $conditions_def), array('default_disabled' => 'disabled'))
            // disable value on store view scope
            ->joinLeft(
                array('e_st_attr' => $this->getTable('catalog/product_attribute_media_gallery_value')),
                join(' AND ', $conditions_store), array('store_disabled' => 'disabled'));

//        die((string)($this->_select));
        $query = $this->_getWriteAdapter()->query($this->_select);

        return $query;
    }

    public function getAllImagesCollection($storeId, $includeOutOfStock = true, $catId = null)
    {

        $store = Mage::app()->getStore($storeId);
        /* @var $store Mage_Core_Model_Store */

        if (!$store) {
            return false;
        }

// filter for category
        if ($catId) {
            $catConditions = array(
                'e.entity_id=cat_index.product_id',
                $this->_getWriteAdapter()->quoteInto('cat_index.store_id=?', $store->getId()),
                $this->_getWriteAdapter()->quoteInto('cat_index.category_id=?', $catId),
                $this->_getWriteAdapter()->quoteInto('cat_index.is_parent=?', 1),
            );
        } else {

            $rootId = $store->getRootCategoryId();

            $_category = Mage::getModel('catalog/category')->load($rootId); //get category model
            $child_categories = $_category->getResource()->getChildren($_category, false); //array consisting of all child categories id
            $child_categories = array_merge(array($rootId), $child_categories);

// filter product that doesn't belongs to the store root category childs
            $catConditions = array(
                'e.entity_id=cat_index.product_id',
                $this->_getWriteAdapter()->quoteInto('cat_index.store_id=?', $store->getId()),
//                $this->_getWriteAdapter()->quoteInto('cat_index.category_id=?', $rootId),
//                $this->_getWriteAdapter()->quoteInto('cat_index.category_id in (?)', $child_categories),
                $this->_getWriteAdapter()->quoteInto('cat_index.position!=?', 0),
            );
        }

        $this->_select = $this->_getWriteAdapter()->select()
            ->from(array('e' => $this->getMainTable()), array($this->getIdFieldName(), 'e.entity_id', 'path' => 'e.value'))
            ->join(
                array('cat_index' => $this->getTable('catalog/category_product_index')), join(' AND ', $catConditions), array());


// filter Out of Stock
        if (!$includeOutOfStock) {
            $stkConditions = array(
                'e.entity_id=stk.product_id',
                $this->_getWriteAdapter()->quoteInto('stk.is_in_stock=?', 1)
            );
            $this->_select = $this->_select->join(
                array('stk' => $this->getTable('cataloginventory/stock_item')), join(' AND ', $stkConditions), array('is_in_stock' => 'is_in_stock'));
        }

//        $valueConditions = array(
//            'e.value_id=w.value_id',
//                // $this->_getWriteAdapter()->quoteInto('w.disabled=?', 0)
//        );
//
//        $this->_select = $this->_select->join(
//                array('w' => $this->getTable('catalog/product_attribute_media_gallery_value')), join(' AND ', $valueConditions), array('w.disabled')
//        );

        $query = $this->_getWriteAdapter()->query($this->_select);

//        die((string) ($this->_select));

        return $query;
    }

}