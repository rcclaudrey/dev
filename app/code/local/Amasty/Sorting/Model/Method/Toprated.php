<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Sorting
 */
class Amasty_Sorting_Model_Method_Toprated extends Amasty_Sorting_Model_Method_Abstract
{
    public function getCode()
    {
        return 'rating_summary';
    }    
    
    public function getName()
    {
        return 'Top Rated';
    }
    
    public function apply($collection, $currDir)  
    {
        if (!$this->isEnabled()){
            return $this;
        }
        
        $collection->joinField(
            $this->getCode(),               // alias
            'review/review_aggregate',      // table
            $this->getCode(),               // field
            'entity_pk_value=entity_id',    // bind
            array(
                'entity_type' => 1, 
                'store_id' => Mage::app()->getStore()->getId()
            ),                              // conditions
            'left'                          // join type
        );
        $collection->getSelect()->order($this->getCode() . ' ' . $currDir);
        
        return $this;
    }
   
}