<?php

/**
 * Description of SitemapEnhanced
 * @package   CueBlocks_SitemapEnhanced
 * @company    CueBlocks - http://www.cueblocks.com/
 
 */
class CueBlocks_SitemapEnhanced_Model_Mysql4_Catalog_Product extends Mage_Sitemap_Model_Mysql4_Catalog_Product
{
    private $_store;
    private $_filterOutOfStock;
    private $_filterInStock;
    private $_filterByCatId;
    private $_alias;

    private $_urlConditions;
    private $_catConditions;
    private $_stkConditions;


    function _setAlias()
    {
        $alias = 'e';

        if (Mage::helper('sitemapEnhanced')->isMageAbove18()) {
            $alias = 'main_table';
        }
        $this->_alias = $alias;

        return $this->_alias;
    }

    /**
     * Get category collection array
     *
     * @param unknown_type $storeId
     * @return array
     */
    public function getCollection($storeId, $filterOutOfStock = false, $filterInStock = false, $filterByCatId = null)
    {

        $store = Mage::app()->getStore($storeId);
        /* @var $store Mage_Core_Model_Store */

        if (!$store) {
            return false;
        }

        // set env. values
        $this->_store = $store;
        $this->_filterOutOfStock = $filterOutOfStock;
        $this->_filterInStock = $filterInStock;
        $this->_filterByCatId = $filterByCatId;

        $storeId = (int)$store->getId();

        // set default alias for query
        $this->_setAlias();
        // set query conditions
        $this->_setConditions();


        $this->_select = $this->_getWriteAdapter()->select()
            ->from(array($this->_alias => $this->getMainTable()), array($this->getIdFieldName(), 'updated_at'))
            ->join(
                array('w' => $this->getTable('catalog/product_website')),
                $this->_alias . '.entity_id=w.product_id',
                array()
            )
            ->where('w.website_id=?', $store->getWebsiteId())
            ->joinLeft(
                array('ur' => $this->getTable('core/url_rewrite')),
                join(' AND ', $this->_urlConditions),
                array('url' => 'request_path')
            );

        if ($this->_catConditions) {
            $this->_select = $this->_select->join(
                array('cat_index' => $this->getTable('catalog/category_product_index')),
                join(' AND ', $this->_catConditions),
                array()
            )
                ->distinct(true);
        }

        if ($this->_stkConditions) {
            $this->_select = $this->_select->join(
                array('stk' => $this->getTable('cataloginventory/stock_item')), $this->_stkConditions, array('is_in_stock','manage_stock','use_config_manage_stock'));
        }

        $this->_addFilter($storeId, 'visibility', Mage::getSingleton('catalog/product_visibility')->getVisibleInSiteIds(), 'in');
        $this->_addFilter($storeId, 'status', Mage::getSingleton('catalog/product_status')->getVisibleStatusIds(), 'in');

//        die((string)($this->_select));

        $query = $this->_getWriteAdapter()->query($this->_select);

        return $query;
    }

    function _setConditions()
    {
        $store = $this->_store;
        $storeId = (int)$store->getId();

        $urlConditions = array(
            $this->_alias . '.entity_id=ur.product_id',
            'ur.category_id IS NULL',
            $this->_getWriteAdapter()->quoteInto('ur.store_id=?', $storeId),
            $this->_getWriteAdapter()->quoteInto('ur.is_system=?', 1),
        );

        $catConditions = null;
// Not good: filter product that doesn't belongs to the store root category childs
//        $rootId = $store->getRootCategoryId();

//            $_category = Mage::getModel('catalog/category')->load($rootId); //get category model
//            $child_categories = $_category->getResource()->getChildren($_category, false); //array consisting of all child categories id
//            $child_categories = array_merge(array($rootId), $child_categories);

//            $catConditions = array(
//                $this->_alias . '.entity_id=cat_index.product_id',
//                $this->_getWriteAdapter()->quoteInto('cat_index.store_id=?', $store->getId()),
//                $this->_getWriteAdapter()->quoteInto('cat_index.category_id=?', $rootId),
//                $this->_getWriteAdapter()->quoteInto('cat_index.category_id in (?)', $child_categories), // skip too many prod ( subcategory)

//                THIS SKIP ALL PRODUCT NOT ASSIGNED TO ANY CATEGORY
//                $this->_getWriteAdapter()->quoteInto('cat_index.position!=?', 0),
//            );

        // filter in/out of stock
        $manageStockConfig = Mage::getStoreConfig('cataloginventory/item_options/manage_stock', $storeId);

        if ($this->_filterOutOfStock) {
            $sql = $this->_alias . '.entity_id=stk.product_id ';

            if ($manageStockConfig) {
                $sql .= ' AND IF (stk.use_config_manage_stock = 1, stk.is_in_stock = 1';
            } else {
                $sql .= ' AND IF (stk.use_config_manage_stock = 1, TRUE';
            }
            $sql .= ' ,(stk.manage_stock = 0 OR (stk.manage_stock = 1 AND stk.is_in_stock = 1)) )';
            $stkConditions = $sql;

            // this is not working fine for manage stock = no
            // while stock_status table is not working for configurable without any
            // associate simple products
//            $cond = array(
//                $this->_getWriteAdapter()->quoteInto('stk.manage_stock=?', 1),
//                $this->_getWriteAdapter()->quoteInto('stk.is_in_stock=?', 1),
//            );
//            $str_cond = '(' . join(' AND ', $cond) . ')';
//
//            $cond_stk = array(
//                $this->_getWriteAdapter()->quoteInto('stk.manage_stock=?', 0),
//                $str_cond
//            );
//            $cond_stk_str = '(' . join(' OR ', $cond_stk) . ')';
//
//            $stkConditions = array(
//                $this->_alias . '.entity_id=stk.product_id',
//                $cond_stk_str
//            );

        } elseif ($this->_filterInStock) {

            $sql = $this->_alias . '.entity_id=stk.product_id ';

            if ($manageStockConfig) {
                $sql .= ' AND IF (stk.use_config_manage_stock = 1, stk.is_in_stock = 0';
            } else {
                $sql .= ' AND IF (stk.use_config_manage_stock = 1, FALSE';
            }
            $sql .= ' ,stk.manage_stock = 1 AND stk.is_in_stock = 0)';
            $stkConditions = $sql;

//            $stkConditions = array(
//                $this->_alias . '.entity_id=stk.product_id',
//                $this->_getWriteAdapter()->quoteInto('stk.manage_stock=?', 1),
//                $this->_getWriteAdapter()->quoteInto('stk.is_in_stock=?', 0)
//            );

        } else {
            $stkConditions = null;
        }

        // filter for category
        if ($this->_filterByCatId) {

            $urlConditions = array(
                $this->_alias . '.entity_id=ur.product_id',
                $this->_getWriteAdapter()->quoteInto('ur.category_id=?', $this->_filterByCatId),
                $this->_getWriteAdapter()->quoteInto('ur.store_id=?', $storeId),
                $this->_getWriteAdapter()->quoteInto('ur.is_system=?', 1),
            );

            $catConditions = array(
                $this->_alias . '.entity_id=cat_index.product_id',
                $this->_getWriteAdapter()->quoteInto('cat_index.store_id=?', $storeId),
                $this->_getWriteAdapter()->quoteInto('cat_index.category_id=?', $this->_filterByCatId),
                $this->_getWriteAdapter()->quoteInto('cat_index.is_parent=?', 1),
            );
        }

        $this->_catConditions = $catConditions;
        $this->_urlConditions = $urlConditions;
        $this->_stkConditions = $stkConditions;
    }
}
