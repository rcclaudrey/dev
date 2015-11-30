<?php
 /**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Sorting
 */

class Amasty_Sorting_Model_Method_Orderview extends Amasty_Sorting_Model_Method_Abstract
{
    public function getCode()
    {
        return 'order_view';
    }

    public function getName()
    {
        return 'Order per View';
    }

    protected function getSorters()
    {
        return array(
            'dividend' => Mage::getModel('amsorting/method_bestselling'),
            'divider'  => Mage::getModel('amsorting/method_mostviewed'),
        );
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

        $sorters = $this->getSorters();

        if (Mage::getStoreConfig('amsorting/general/use_index')) {
            foreach ($sorters as $sorter) {
                $collection->joinField(
                    $sorter->getCode(),      // alias
                    $sorter->getIndexTable(),// table
                    $sorter->getCode(),      // field
                    'id=entity_id',          // bind
                    array('store_id' => Mage::app()->getStore()->getId()),
                    // conditions
                    'left'                 // join type
                );
            }
        }
        else {
            $select = $collection->getSelect();
            $col   = $select->getPart('columns');

            foreach ($sorters as $sorter) {
                $col[] = array('', $sorter->getColumnSelect(), $sorter->getCode());
            }

            $select->setPart('columns', $col);

        }
        $collection->getSelect()->order(
            new Zend_Db_Expr(
                '('.$sorters['dividend']->getCode() . '/'
                . $sorters['divider']->getCode() . ') ' . $currDir
            )
        );

        return $this;

    }


}