<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Sorting
 */
class Amasty_Sorting_Model_Method_Revenue extends Amasty_Sorting_Model_Method_Abstract
{
    protected $isAllGrouped = false;
    
    public function getCode()
    {
        return 'revenue';
    }    
    
    public function getName()
    {
        return 'Revenue';
    }
    
    public function getIndexTable()
    {
        return 'amsorting/' . $this->getCode();
    } 
    
    public function getColumnSelect()
    {
        $sql =' SELECT SUM(order_item.row_total)'
            . ' FROM ' . Mage::getSingleton('core/resource')->getTableName('sales/order_item') 
            . ' AS order_item';

        // exclude items (products)
        $exclude = Mage::getStoreConfig('amsorting/general/exclude');
        if ($exclude) {
            $sql .= ' LEFT JOIN ' . Mage::getSingleton('core/resource')->getTableName('sales/order')
                 .  ' AS flat_orders'
                 .  '   ON (flat_orders.entity_id = order_item.order_id) ';
        }

        if ($this->isAllGrouped){  
            $sql .= ' INNER JOIN ' . Mage::getSingleton('core/resource')->getTableName('sales/quote_item_option') 
                 .  ' AS order_item_option'
                 .  '   ON (order_item.item_id = order_item_option.item_id AND order_item_option.code="product_type")'
                 .  ' WHERE order_item_option.product_id = e.entity_id ' 
            ;
        }  
        else {
            $sql .= ' WHERE order_item.product_id = e.entity_id ';
        }

        if ($exclude) {
            $temp = explode(',', $exclude);
            $exclude = implode('\',\'', $temp);
            $sql .= ' AND flat_orders.state NOT IN (\'' . $exclude . '\')';
        }

        $sql .= $this->getPeriodCondition('order_item.created_at', 'best_revenue_period');
        $sql .= $this->getStoreCondition('order_item.store_id');
        
        
        return new Zend_Db_Expr('(' . $sql . ')');         
    }    
     
    public function getIndexSelect() 
    {
        $sql =' SELECT product_id, order_item.store_id, SUM(row_total)'
            . ' FROM ' . Mage::getSingleton('core/resource')->getTableName('sales/order_item') . ' AS order_item';

        // exclude items (products)
        $exclude = Mage::getStoreConfig('amsorting/general/exclude');
        $temp = explode(',', $exclude);
        $exclude = implode('\',\'', $temp);
        if ($exclude) {
            $sql .= ' LEFT JOIN ' . Mage::getSingleton('core/resource')->getTableName('sales/order')
                 .  ' AS flat_orders'
                 .  '   ON (flat_orders.entity_id = order_item.order_id) '
                 .  ' WHERE flat_orders.state NOT IN (\'' . $exclude . '\')';
        } else {
            $sql .= ' WHERE 1 ';
        }

        $sql .= $this->getPeriodCondition('order_item.created_at', 'best_revenue_period')
             . ' GROUP BY product_id, store_id'
        ;

        if ($this->isAllGrouped){
            $sql =' SELECT order_item_option.product_id, order_item.store_id, SUM(order_item.qty_ordered)'
                 .  ' FROM ' . Mage::getSingleton('core/resource')->getTableName('sales/order_item') . ' AS order_item';
            if ($exclude) {
                $sql .= ' LEFT JOIN ' . Mage::getSingleton('core/resource')->getTableName('sales/order')
                     .  ' AS flat_orders'
                     .  '   ON (flat_orders.entity_id = order_item.order_id) '
                     .  ' WHERE flat_orders.state NOT IN (\'' . $exclude . '\')';
            }
            $sql .= ' INNER JOIN ' . Mage::getSingleton('core/resource')->getTableName('sales/quote_item_option')
                 .  ' AS order_item_option'
                 .  '   ON (order_item.item_id = order_item_option.item_id AND order_item_option.code="product_type")';
            if (!$exclude) {
                $sql .= ' WHERE 1 ';
            }
            $sql .= $this->getPeriodCondition('order_item.created_at', 'best_revenue_period')
                 . ' GROUP BY order_item_option.product_id, order_item.store_id'
            ;
        }
        
        return $sql;
    }   
    

    public function apply($collection, $currDir)  
    {
        $attr = Mage::getStoreConfig('amsorting/general/best_attr');
        if ($attr) {
            $collection->addAttributeToSort($attr, $currDir);
        }
        return parent::apply($collection, $currDir);
    }
   
}