<?php
 /**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Pgrid
 */

class Amasty_Pgrid_Model_Mysql4_Groupattribute_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{

    public function _construct()
    {
        $this->_init('ampgrid/groupattribute');
    }

    public function getCollectionAttribute($groupId)
    {

        $cond = array(
            'ea.attribute_id = main_table.attribute_id',
            $this->_getConditionSql('ea.attribute_code', array('in' => $this->getResource()->getNotEditableAttributes()))
        );
        $this->getSelect()
            ->joinLeft(
                array('ea' => $this->getTable('eav/attribute')),
                implode(' AND ', $cond), array(
                    'allow_to_edit' => $this->getCheckSql(
                        'ea.attribute_id IS NOT NULL', '0',
                        '1'),
                )
            )
        ->where('group_id = ?', $groupId);
        return $this;
    }

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