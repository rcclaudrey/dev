<?php

/**
 * Description of SitemapEnhanced
 * @package   CueBlocks_SitemapEnhanced
 * @company   CueBlocks - http://www.cueblocks.com/
 
 */
class CueBlocks_SitemapEnhanced_Model_Mysql4_Catalog_Tag extends Mage_Core_Model_Mysql4_Abstract
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
        $this->_init('tag/summary', 'tag_id');
    }

    /**
     * Get Tag for storeView
     *
     * @param unknown_type $storeId
     * @return array
     */
    public function getCollection($storeId)
    {

        if (trim($storeId) == '') {
            return false;
        }

        $relConditions = array(
            $this->_getWriteAdapter()->quoteInto('r.active=?', 1),
            'r.tag_id=t.tag_id'
        );

        $this->_select = $this->_getWriteAdapter()->select()
                ->from(array('t' => $this->getMainTable()), array('t.tag_id'))
                ->where('t.store_id =?', $storeId)
                ->join(
                array('r' => $this->getTable('tag/relation')), join(' AND ', $relConditions), array());


        $query = $this->_getWriteAdapter()->query($this->_select);

//        die((string) ($this->_select));

        return $query;
    }

}