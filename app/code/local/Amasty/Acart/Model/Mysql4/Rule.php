<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Acart
 */ 
class Amasty_Acart_Model_Mysql4_Rule extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        $this->_init('amacart/rule', 'rule_id');
    }
    
    /**
     * Return codes of all product attributes currently used in promo rules
     *
     * @return array
     */
    public function getAttributes()
    {
        $read = $this->_getReadAdapter();
        $tbl   = $this->getTable('amacart/attribute');
        
        $select = $read->select()->from($tbl, new Zend_Db_Expr('DISTINCT code'));
        return $read->fetchCol($select);
    }

    /**
     * Save product attributes currently used in conditions and actions of the rule
     *
     * @param int $id rule id
     * @param mixed $attributes
     * return Amasty_Shiprestriction_Model_Mysql4_Rule
     */
    public function saveAttributes($id, $attributes)
    {
        $write = $this->_getWriteAdapter();
        $tbl   = $this->getTable('amacart/attribute');
        
        $write->delete($tbl, array('rule_id=?' => $id));
        
        $data = array();
        foreach ($attributes as $code){
            $data[] = array(
                'rule_id' => $id,
                'code'    => $code,
            );
        }
        $write->insertMultiple($tbl, $data);
        
        return $this;
    } 
    
}