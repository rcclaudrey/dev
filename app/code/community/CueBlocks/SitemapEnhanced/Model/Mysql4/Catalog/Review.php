<?php

/**
 * Description of SitemapEnhanced
 * @package   CueBlocks_SitemapEnhanced
 * @company   CueBlocks - http://www.cueblocks.com/
 
 */
class CueBlocks_SitemapEnhanced_Model_Mysql4_Catalog_Review extends Mage_Core_Model_Mysql4_Abstract
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
        $this->_init('review/review', 'review_id');
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

        $conditions = array(
            'r.review_id =detail.review_id',
            $this->_getWriteAdapter()->quoteInto('detail.store_id=?', $storeId)
        );

        $this->_select = $this->_getWriteAdapter()->select()
            ->from(array('r' => $this->getMainTable()), array('prod_id' => 'r.entity_pk_value'))
            ->join(
                array('detail' => $this->getTable('review/review_detail')), join(' AND ', $conditions), array())
            ->where('r.status_id =?', 1)
            ->distinct(true);

        $this->_addFilter($storeId, 'visibility', Mage::getSingleton('catalog/product_visibility')->getVisibleInSiteIds(), 'in');

//        die((string) ($this->_select));

        $query = $this->_getWriteAdapter()->query($this->_select);

        return $query;
    }

    /**
     * Add attribute to filter
     *
     * @param int $storeId
     * @param string $attributeCode
     * @param mixed $value
     * @param string $type
     * @return Zend_Db_Select
     */
    protected function _addFilter($storeId, $attributeCode, $value, $type = '=')
    {
        if (!isset($this->_attributesCache[$attributeCode])) {
            $attribute = Mage::getSingleton('catalog/product')->getResource()->getAttribute($attributeCode);

            $this->_attributesCache[$attributeCode] = array(
                'entity_type_id' => $attribute->getEntityTypeId(),
                'attribute_id' => $attribute->getId(),
                'table' => $attribute->getBackend()->getTable(),
                'is_global' => $attribute->getIsGlobal() == Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
                'backend_type' => $attribute->getBackendType()
            );
        }

        $attribute = $this->_attributesCache[$attributeCode];

        if (!$this->_select instanceof Zend_Db_Select) {
            return false;
        }

        switch ($type) {
            case '=':
                $conditionRule = '=?';
                break;
            case 'in':
                $conditionRule = ' IN(?)';
                break;
            default:
                return false;
                break;
        }

        $this->_select->join(
            array('t1_' . $attributeCode => $attribute['table']), 'r.entity_pk_value=t1_' . $attributeCode . '.entity_id AND t1_' . $attributeCode . '.store_id=0', array()
        )
            ->where('t1_' . $attributeCode . '.attribute_id=?', $attribute['attribute_id']);

        if ($attribute['is_global']) {
            $this->_select->where('t1_' . $attributeCode . '.value' . $conditionRule, $value);
        } else {
            $ifCase = $this->getCheckSql('t2_' . $attributeCode . '.value_id > 0', 't2_' . $attributeCode . '.value', 't1_' . $attributeCode . '.value');
            $this->_select->joinLeft(
                array('t2_' . $attributeCode => $attribute['table']), $this->_getWriteAdapter()->quoteInto('t1_' . $attributeCode . '.entity_id = t2_' . $attributeCode . '.entity_id AND t1_' . $attributeCode . '.attribute_id = t2_' . $attributeCode . '.attribute_id AND t2_' . $attributeCode . '.store_id=?', $storeId), array()
            )
                ->where('(' . $ifCase . ')' . $conditionRule, $value);
        }


        return $this->_select;
    }

    /**
     * FOR COMPATIBILITY WITH 1.5 and 1.4
     * Generate fragment of SQL, that check condition and return true or false value
     *
     * @param Zend_Db_Expr|Zend_Db_Select|string $expression
     * @param string $true  true value
     * @param string $false false value
     */
    public function getCheckSql($expression, $true, $false)
    {
        if ($expression instanceof Zend_Db_Expr || $expression instanceof Zend_Db_Select) {
            $expression = sprintf("IF((%s), %s, %s)", $expression, $true, $false);
        } else {
            $expression = sprintf("IF(%s, %s, %s)", $expression, $true, $false);
        }

        return new Zend_Db_Expr($expression);
    }
}