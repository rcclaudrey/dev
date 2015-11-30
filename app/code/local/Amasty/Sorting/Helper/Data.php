<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Sorting
 */
class Amasty_Sorting_Helper_Data extends Mage_Core_Helper_Abstract 
{
    public function getMethods()
    {
//        $isSearch = in_array(Mage::app()->getRequest()->getModuleName(), array('sqli_singlesearchresult', 'catalogsearch')); 
//        if ($isSearch)
//            return array();
        
        
        // class names. order defines the position in the dropdown
        $methods = array(
            'new',    
            'saving',
            'bestselling',    
            'mostviewed',    
            'toprated',    
            'commented',    
            'wished',
            'qty',
            'profit',
            'revenue',
            'revenueview',
            'orderview',
        ); 

        return $methods;
    }
}