<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Sorting
 */
class Amasty_Sorting_Model_Method_Profit extends Amasty_Sorting_Model_Method_Abstract
{
    public function getCode()
    {
        return 'profit';
    }

    public function getName()
    {
        return 'Profit';
    }

    /**
     * @param Mage_Catalog_Model_Resource_Product_Collection $collection
     * @param string $currDir
     *
     * @return $this
     */
    public function apply($collection, $currDir)
    {
        if (!$this->isEnabled()){
            return $this;
        }

        $alias = 'price_index';
        if (preg_match('/`([a-z0-9\_]+)`\.`final_price`/', $collection->getSelect()->__toString(), $m)){
            $alias = $m[1];
        }

        $price = Mage::getStoreConfig('amsorting/general/profit_price');
        $attribute = Mage::getStoreConfig('amsorting/general/product_attribute');
        $collection->joinAttribute('cost', Mage_Catalog_Model_Product::ENTITY.'/'.$attribute, 'entity_id', null, 'left', Mage::app()->getStore()->getId());
        $collection->getSelect()->columns(array('profit' => "(`{$alias}`.`{$price}` - IF(`at_cost`.`value` IS NULL, 0, `at_cost`.`value`))"));
        $collection->getSelect()->order("profit $currDir");

        return $this;
    }
}