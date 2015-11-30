<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Sorting
 */
class Amasty_Sorting_Model_Method_Image extends Amasty_Sorting_Model_Method_Abstract
{
    public function apply($collection, $currDir)  
    {
        $show = Mage::getStoreConfig('amsorting/general/no_image_last');
        if (!$show)
            return $this;
            
        //skip search results    
        $isSearch = in_array(Mage::app()->getRequest()->getModuleName(), array('sqli_singlesearchresult', 'catalogsearch')); 
        if ($isSearch && 2 == $show)
            return $this;
        
        // will be skipped for flat catalog    
        $collection->addAttributeToSort('small_image','asc');
        
        $orders = $collection->getSelect()->getPart(Zend_Db_Select::ORDER);

        // move from the last to the the first position
        $last = array_pop($orders);
        $last[0] = new Zend_Db_Expr('IF(IFNULL(`small_image`, "no_selection")="no_selection", 1, 0)');
        array_unshift($orders, $last);

        $collection->getSelect()->setPart(Zend_Db_Select::ORDER, $orders); 
        
        return $this;
    }
}