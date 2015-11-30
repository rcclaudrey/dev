<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Sorting
 */
class Amasty_Sorting_Model_Method_Mostviewed extends Amasty_Sorting_Model_Method_Abstract
{
    public function getCode()
    {
        return 'most_viewed';
    }    
    
    public function getName()
    {
        return 'Most Viewed';
    }
    
    public function getIndexTable()
    {
        return 'amsorting/' . $this->getCode();
    } 
    
    public function getColumnSelect()
    {
        $sql = '';
        if (version_compare(Mage::getVersion(), '1.4') >= 0) { 
            $sql =' SELECT COUNT(*)'
                . ' FROM ' . Mage::getSingleton('core/resource')->getTableName('reports/event') . ' AS viewed_item'
                . ' WHERE viewed_item.object_id = e.entity_id AND viewed_item.event_type_id = ' . Mage_Reports_Model_Event::EVENT_PRODUCT_VIEW 
                . $this->getPeriodCondition('viewed_item.logged_at', 'viewed_period') 
                . $this->getStoreCondition('viewed_item.store_id') 
            ;
        }
        else { // old 
            $sql =' SELECT COUNT(*)'
                . ' FROM ' . Mage::getSingleton('core/resource')->getTableName('reports/viewed_product_index') . ' AS viewed_item'
                . ' WHERE viewed_item.product_id = e.entity_id' 
                . $this->getPeriodCondition('viewed_item.added_at', 'viewed_period') 
            ;
        }
        return new Zend_Db_Expr('(' . $sql . ')');       
    }

    public function getIndexSelect()
    {
        if (version_compare(Mage::getVersion(), '1.4') >= 0) {
            $sql =' SELECT object_id, store_id, COUNT(*)'
                . ' FROM ' . Mage::getSingleton('core/resource')->getTableName('reports/event') . ' AS viewed_item'
                . ' WHERE viewed_item.event_type_id = ' . Mage_Reports_Model_Event::EVENT_PRODUCT_VIEW
                . $this->getPeriodCondition('viewed_item.logged_at', 'viewed_period')
                . ' GROUP BY object_id, store_id'
            ;
        } else {
            // there is no indexes in the 1.3 version, so we do not check.
            $sql =' SELECT product_id, store_id, COUNT(*)'
                . ' FROM ' . Mage::getSingleton('core/resource')->getTableName('reports/viewed_product_index') . ' AS viewed_item'
                . ' WHERE 1 '
                . $this->getPeriodCondition('viewed_item.added_at', 'viewed_period')
                . ' GROUP BY product_id, store_id'
            ;
        }
        return $sql;
    }

    public function apply($collection, $currDir)  
    {
        $attr = Mage::getStoreConfig('amsorting/general/viewed_attr');
        if ($attr) {
            $collection->addAttributeToSort($attr, $currDir);
        }
        return parent::apply($collection, $currDir);
    }
}