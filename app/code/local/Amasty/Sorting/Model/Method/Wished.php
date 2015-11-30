<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Sorting
 */
class Amasty_Sorting_Model_Method_Wished extends Amasty_Sorting_Model_Method_Abstract
{
    public function getCode()
    {
        return 'wished';
    }    
    
    public function getName()
    {
        return 'Now in Wishlists';
    }
    
    public function getIndexTable()
    {
        return 'amsorting/' . $this->getCode();
    } 
    
    public function getColumnSelect()
    {
        $sql =' SELECT COUNT(*)'
            . ' FROM ' . Mage::getSingleton('core/resource')->getTableName('wishlist/item')  . ' AS wishlist_item'
            . ' WHERE wishlist_item.product_id = e.entity_id ' 
            . $this->getStoreCondition('wishlist_item.store_id') 
        ;        
        return new Zend_Db_Expr('(' . $sql . ')');         
    }    
     
    public function getIndexSelect() 
    {
        $sql =' SELECT product_id, store_id, COUNT(*)'
            . ' FROM ' . Mage::getSingleton('core/resource')->getTableName('wishlist/item')
            . ' GROUP BY product_id, store_id'
        ;
        return $sql;
    }    
   
}