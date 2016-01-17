<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Xsearch
 */
class Amasty_Xsearch_Model_Fulltext extends Mage_CatalogSearch_Model_Mysql4_Fulltext
{
    protected function _construct()
    {
        $this->_init('amxsearch/fulltext', 'product_id');
        $this->_engine = Mage::helper('catalogsearch')->getEngine();
    }
    
    function cleanIndex($storeId = null, $productId = null)
    {
        
       $where = array();

        if (!is_null($storeId)) {
            $where[] = $this->_getWriteAdapter()->quoteInto('store_id=?', $storeId);
        }
        if (!is_null($productId)) {
            $where[] = $this->_getWriteAdapter()->quoteInto('product_id IN (?)', $productId);
        }

        $this->_getWriteAdapter()->delete($this->getMainTable(), $where);

        return $this;

    }
    
    protected function _getAttributeCol($configAttributes){
        return 'col_'.$configAttributes['order'];
    }
        
    protected function _prepareProductIndex($indexData, $productData, $storeId)
    {
        $index = array();

        foreach ($this->_getSearchableAttributes('static') as $attribute) {
            $attributeCode = $attribute->getAttributeCode();

            if (isset($productData[$attributeCode])) {
                $value = $this->_getAttributeValue($attribute->getId(), $productData[$attributeCode], $storeId);
                if ($value) {
                    //For grouped products
                    if (isset($index[$attributeCode])) {
                        if (!is_array($index[$attributeCode])) {
                            $index[$attributeCode] = array($index[$attributeCode]);
                        }
                        $index[$attributeCode][] = $value;
                    }
                    //For other types of products
                    else {
                        $index[$attributeCode] = $value;
                    }
                }
            }
        }

        foreach ($indexData as $entityId => $attributeData) {
            foreach ($attributeData as $attributeId => $attributeValue) {
                $value = $this->_getAttributeValue($attributeId, $attributeValue, $storeId);
                if (!is_null($value) && $value !== false) {
                    $attributeCode = $this->_getSearchableAttribute($attributeId)->getAttributeCode();

                    if (isset($index[$attributeCode])) {
                        $index[$attributeCode][$entityId] = $value;
                    } else {
                        $index[$attributeCode] = array($entityId => $value);
                    }
                }
            }
        }
        
        return $index;
    }
    
    protected function _getConfigAttributes(){
        $ret = array();
        
        $count = 10;
        $weight = 10;
        $step = $weight / $count;

        for($i = 1; $i <= $count; $i++){
            $attrbiute = Mage::getStoreConfig('amxsearch/attributes/attribute_' . $i);
            if (!empty($attrbiute)){
//                $weight = Mage::getStoreConfig('amxsearch/weights/weight_' . $i);
                
                $ret[$attrbiute] = array(
                    'weight' => $weight,
                    'order' => $i
                );
                
                $weight -= $step;
            }
        }
        
        return $ret;
    }
    
    protected function _saveProductIndexes($storeId, $productIndexes)
    {
        $configAttributes = $this->_getConfigAttributes();
        
        
        foreach($productIndexes as $entityId => $index){
            
            $attributes = array();
            
            foreach($index as $col => $val){
                if (isset($configAttributes[$col])){
                    $attributeCol = $this->_getAttributeCol($configAttributes[$col]);

                    if (is_array($val)){
                        foreach($val as $i => $v){
                            $val[$i] = str_replace($this->_separator, '', $val[$i]);
                        }
                        $val = implode($this->_separator, $val);
                    }

                    $attributes[$attributeCol] = $val;
                }
            }
            
            

            $this->_getWriteAdapter()->insert($this->getMainTable(), array_merge(array(
                'product_id'    => $entityId,
                'store_id'      => $storeId,
                'data_index' => implode($this->_separator, $attributes)
            ), $attributes));
        }
        
    }
    
    protected function _getSearchableAttributes($backendType = null)
    {
        if (is_null($this->_searchableAttributes)) {
            $configAttributes = $this->_getConfigAttributes();
            
            $this->_searchableAttributes = array();

            $productAttributeCollection = Mage::getResourceModel('catalog/product_attribute_collection');

            $productAttributeCollection->addFieldToFilter('attribute_code', array(
                'in' => array_merge(array_keys($configAttributes), array(
                    'visibility', 'status'
                ))
            ));
            
            $attributes = $productAttributeCollection->getItems();

            Mage::dispatchEvent('catelogsearch_searchable_attributes_load_after', array(
                'engine' => $this->_engine,
                'attributes' => $attributes
            ));

            $entity = $this->getEavConfig()
                ->getEntityType(Mage_Catalog_Model_Product::ENTITY)
                ->getEntity();

            foreach ($attributes as $attribute) {
                $attribute->setEntity($entity);
            }

            $this->_searchableAttributes = $attributes;
        }

        if (!is_null($backendType)) {
            $attributes = array();
            foreach ($this->_searchableAttributes as $attributeId => $attribute) {
                if ($attribute->getBackendType() == $backendType) {
                    $attributes[$attributeId] = $attribute;
                }
            }

            return $attributes;
        }

        return $this->_searchableAttributes;
    }
    
    function _getRelevanceCol($preparedTerms, $queryText){
        
        $cond = 0;
        
        $configAttributes = $this->_getConfigAttributes();
        
        $pies = array();
        $repl = '(MATCH (`:col_name`) AGAINST (:query) * :weight)';
        $repl .= ' + if (LOCATE(LCASE(:query_text), LCASE(`:col_name`)) != 0, 1 * :weight, 0)';
        
        foreach($configAttributes as $code => $attributeData){
            
            $weight = $attributeData['weight'];
            
            if ($weight > 0){
                foreach($preparedTerms as $term){
                    
                    if (!empty($term)){
                    
                        $pies[] = strtr($repl, array(
                            ':col_name' => $this->_getAttributeCol($attributeData),
                            ':query' => $this->_getWriteAdapter()->quote($term),
                            ':query_text' => $this->_getWriteAdapter()->quote($queryText),
                            ':weight' => $weight
                        ));
                    }
                }
            }
        }
        
        if (count($pies) > 0)
            $cond = implode(' + ', $pies);
        
        return new Zend_Db_Expr($cond);
    }
    
    public function getSearchType($storeId = null)
    {
        return Mage::getStoreConfig('amxsearch/search/search_type');
    }
    
    public function prepareResult($object, $queryText, $query)
    {

        $adapter = $this->_getWriteAdapter();
        if (!$query->getIsProcessed() && !empty($queryText)) {
            $searchType = $this->getSearchType($query->getStoreId());

            $preparedTerms = $this->prepareTerms($queryText, $query->getMaxQueryWords());

            $bind = array();
            $like = array();
            $likeCond  = '';
            if ($searchType == Mage_CatalogSearch_Model_Fulltext::SEARCH_TYPE_LIKE
                || $searchType == Mage_CatalogSearch_Model_Fulltext::SEARCH_TYPE_COMBINE
            ) {
//                $helper = Mage::getResourceHelper('core');
                $words = Mage::helper('core/string')->splitWords($queryText, true, $query->getMaxQueryWords());
                foreach ($words as $word) {
                    $like[] = $this->getCILike('s.data_index', $word, array('position' => 'any'));
                }
                if ($like) {
                    $likeCond = '(' . join(' OR ', $like) . ')';
                }
            }
            $mainTableAlias = 's';
            $fields = array(
                'query_id' => new Zend_Db_Expr($query->getId()),
                'product_id',
            );
            $select = $adapter->select()
                ->from(array($mainTableAlias => $this->getMainTable()), $fields)
                ->joinInner(array('e' => $this->getTable('catalog/product')),
                    'e.entity_id = s.product_id',
                    array())
                ->where($mainTableAlias.'.store_id = ?', (int)$query->getStoreId());
            
            if ($searchType == Mage_CatalogSearch_Model_Fulltext::SEARCH_TYPE_FULLTEXT
                || $searchType == Mage_CatalogSearch_Model_Fulltext::SEARCH_TYPE_COMBINE
            ) {
                $bind[':query'] = implode(' ', $preparedTerms[0]);
                $where = new Zend_Db_Expr('MATCH ('.$mainTableAlias.'.data_index) AGAINST (:query IN BOOLEAN MODE)');
                
//                Mage::getResourceHelper('catalogsearch')
//                    ->chooseFulltext($this->getMainTable(), $mainTableAlias, $select);
            }

            if ($likeCond != '' && $searchType == Mage_CatalogSearch_Model_Fulltext::SEARCH_TYPE_COMBINE) {
                    $where .= ($where ? ' OR ' : '') . $likeCond;
            } elseif ($likeCond != '' && $searchType == Mage_CatalogSearch_Model_Fulltext::SEARCH_TYPE_LIKE) {
                $where = $likeCond;
            }
            
            $select->columns(array(
                'relevance'  => $this->_getRelevanceCol($preparedTerms[0], $queryText)
            ));

            if ($where != '') {
                $select->where($where);
            }

//            $sql = $adapter->insertFromSelect($select,
//                $this->getTable('catalogsearch/result'),
//                array(),
//                Varien_Db_Adapter_Interface::INSERT_ON_DUPLICATE);
            
            $sql = "INSERT INTO `{$this->getTable('catalogsearch/result')}` "
                . $select 
                . " ON DUPLICATE KEY UPDATE `relevance`=VALUES(`relevance`)";
            
            $adapter->query($sql, $bind);

            $query->setIsProcessed(1);
        }

        return $this;
    }
    
    /**
     * Prepare Terms
     *
     * @param string $str The source string
     * @return array(0=>words, 1=>terms)
     */
    function prepareTerms($str, $maxWordLength = 0)
    {
        $boolWords = array(
            '+' => '+',
            '-' => '-',
            '|' => '|',
            '<' => '<',
            '>' => '>',
            '~' => '~',
            '*' => '*',
        );
        $brackets = array(
            '('       => '(',
            ')'       => ')'
        );
        $words = array(0=>"");
        $terms = array();
        preg_match_all('/([\(\)]|[\"\'][^"\']*[\"\']|[^\s\"\(\)]*)/uis', $str, $matches);
        $isOpenBracket = 0;
        foreach ($matches[1] as $word) {
            $word = trim($word);
            if (strlen($word)) {
                $word = str_replace('"', '', $word);
                $isBool = in_array(strtoupper($word), $boolWords);
                $isBracket = in_array($word, $brackets);
                if (!$isBool && !$isBracket) {
                    $terms[$word] = $word;
                    $word = '"'.$word.'"';
                    $words[] = $word;
                } else if ($isBracket) {
                    if ($word == '(') {
                        $isOpenBracket++;
                    } else {
                        $isOpenBracket--;
                    }
                    $words[] = $word;
                } else if ($isBool) {
                    $words[] = $word;
                }
            }
        }
        if ($isOpenBracket > 0) {
            $words[] = sprintf("%')".$isOpenBracket."s", '');
        } else if ($isOpenBracket < 0) {
            $words[0] = sprintf("%'(".$isOpenBracket."s", '');
        }
        if ($maxWordLength && count($terms) > $maxWordLength) {
            $terms = array_slice($terms, 0, $maxWordLength);
        }
        $result = array($words, $terms);
        return $result;
    }
    
    public function getCILike($field, $value, $options = array())
    {
        $quotedField = $this->_getReadAdapter()->quoteIdentifier($field);
        $sql = "";
        $x = $value;
        $termsArr = preg_split('/\s+/',(string)$x);
        foreach ($termsArr as $term){
            $term = preg_replace('/%/','',$term);
            $term = preg_replace("/'/",'',$term);
            if (empty($sql)){
                $sql = " ($quotedField LIKE " . $this->addLikeEscape($term, $options) . ")";
            }else{
                $sql .= " AND ($quotedField LIKE " . $this->addLikeEscape($term, $options) . ")";
            }
        }

        $res = new Zend_Db_Expr($sql);
        return $res;
    }
    
    public function addLikeEscape($value, $options = array())
    {
        $value = $this->escapeLikeValue($value, $options);
        return new Zend_Db_Expr($this->_getReadAdapter()->quote($value));
    }
    
    public function escapeLikeValue($value, $options = array())
    {
        $value = str_replace('\\', '\\\\', $value);

        $from = array();
        $to = array();
        if (empty($options['allow_symbol_mask'])) {
            $from[] = '_';
            $to[] = '\_';
        }
        if (empty($options['allow_string_mask'])) {
            $from[] = '%';
            $to[] = '\%';
        }
        if ($from) {
            $value = str_replace($from, $to, $value);
        }

        if (isset($options['position'])) {
            switch ($options['position']) {
                case 'any':
                    $value = '%' . $value . '%';
                    break;
                case 'start':
                    $value = $value . '%';
                    break;
                case 'end':
                    $value = '%' . $value;
                    break;
            }
        }

        return $value;
    }

}
?>