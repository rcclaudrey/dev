<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Sorting
 */
class Amasty_Sorting_Block_Catalog_Product_List_Toolbar extends Amasty_Sorting_Block_Catalog_Product_List_Toolbar_Pure
{
    protected $methods = null;
    
    public function getMethods()
    {
        if (is_null($this->methods)){
            $this->methods = array();
            foreach (Mage::helper('amsorting')->getMethods() as $className){
                $method = Mage::getSingleton('amsorting/method_' . $className);  
                $this->methods[$method->getCode()] = $method;
            }   
        }
        
        return $this->methods;
    }    
    
    protected function _construct() 
    {
        parent::_construct();
        
        if ($this->reverse($this->_orderField))
            $this->_direction = 'desc';
    }

    public function getOrderUrl($order, $direction)
    {
        if ($order && $this->reverse($order)) {
            $direction =  'desc'; 
        }
        return parent::getOrderUrl($order, $direction);
    }
    
    public function getCurrentDirection()
    {
        $dir = parent::getCurrentDirection();
        $url = strtolower($this->getRequest()->getParam($this->getDirectionVarName()));   
        if (!$url && $this->reverse($this->getCurrentOrder())){
            $dir = 'desc';
        }    
        return $dir;
    }  

    public function setCollection($collection)
    {
        parent::setCollection($collection);   

        if ($collection->getFlag('amsorting'))  
            return $this;

        // no image sorting will be the first or the second (after stock). LIFO queue
        $hasImage = Mage::getSingleton('amsorting/method_image');
        $hasImage->apply($collection, '');

        // in stock sorting will be first, as the method always moves it's paremater first. LIFO queue
        $inStock = Mage::getSingleton('amsorting/method_instock');
        $inStock->apply($collection, '');
        
        $methods = $this->getMethods();
        if (isset($methods[$this->getCurrentOrder()])){
            $methods[$this->getCurrentOrder()]->apply($collection, $this->getCurrentDirection());
            $collection->getSelect()->order('cat_index_position ' . $this->getCurrentDirection());
            if (Mage::getStoreConfig('amsorting/general/sort_by_id')) {
                $collection->getSelect()->order('e.entity_id ' . $this->getCurrentDirection());
            }
        }

        if ($this->getRequest()->getParam('debug')){
            echo $collection->getSelect();
        }
        
        $collection->setFlag('amsorting',1);
        
        return $this;
    }
    
    protected function reverse($order)
    {
        $methods = $this->getMethods();
        if (isset($methods[$order])){
            return true;
        }
        
        $attr = Mage::getStoreConfig('amsorting/general/desc_attributes');
        if ($attr){
            return in_array($order, explode(',', $attr));
            
        }
        
        return false;
    }
}