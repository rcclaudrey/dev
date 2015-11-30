<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Sorting
 */
class Amasty_Sorting_Model_Method_New extends Amasty_Sorting_Model_Method_Abstract
{
    public function getCode()
    {
        return 'created_at';
    }    
    
    public function getName()
    {
        return 'New';
    }
    
    public function apply($collection, $currDir)  
    {
        if (!$this->isEnabled()){
            return $this;
        }

        $new = false;
        $orders = $collection->getSelect()->getPart(Zend_Db_Select::ORDER);
        foreach ($orders as $k => $v){
			if (is_object($v)) {
				continue;
			} elseif (false !== strpos($v[0], 'created_at')){
                $new = $k;
            }
        }

        $attr = Mage::getStoreConfig('amsorting/general/new_attr');
        if ($attr) {
            if ($new) {
                $orders[$new] = null;
                unset($orders[$new]);
            }
            $collection->getSelect()->setPart(Zend_Db_Select::ORDER, $orders);
            $collection->addAttributeToSort($attr, $currDir);
        } elseif (!$new) {
            $collection->addAttributeToSort('created_at', $currDir);
        }
        
        return $this;
    }
   
}