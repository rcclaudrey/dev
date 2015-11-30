<?php
 /**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Pgrid
 */

/**
 * Class Amasty_Pgrid_Model_Column
 *
 * @method string getCode
 * @method int getWidth
 * @method int getColumnType
 * @method int getIsEditable
 * @method int getIsVisible
 * @method int getColumnId
 * @method int getGroupId
 * @method int getGroupColumnId
 * @method string getType
 * @method string getIndex
 *
 */
class Amasty_Pgrid_Model_Column extends Mage_Core_Model_Abstract
{
    const STANDARD_COLUMN = 'standard';
    const EXTRA_COLUMN = 'extra';

    public function _construct()
    {
        $this->_init('ampgrid/column');
    }

    /**
     *
     * @param int $groupId
     * @return Amasty_Pgrid_Model_Mysql4_Column_Collection
     */
    public function getCollectionStandard($groupId = null)
    {
        return $this->_getCollection(self::STANDARD_COLUMN, $groupId);
    }

    /**
     * @param int $groupId
     *
     * @return Amasty_Pgrid_Model_Mysql4_Column_Collection
     */
    public function getCollectionExtra($groupId = null)
    {
        return $this->_getCollection(self::EXTRA_COLUMN, $groupId);
    }

    /**
     * @param int $groupId
     *
     * @return Amasty_Pgrid_Model_Mysql4_Column_Collection
     */
    public function getCollectionAll($groupId = null)
    {
        $groupId = $groupId ? $groupId : Mage::helper('ampgrid')->getSelectedGroupId();
        $collection = $this->getCollection();
        $collection->getSelect()->joinLeft(
            array('gc' => $collection->getTable('grid_group_column')),
            sprintf('main_table.entity_id = gc.column_id AND gc.group_id = %d', $groupId)
        );
        return $collection;
    }

    /**
     * @param string $columnType
     * @param int $groupId
     *
     * @return Amasty_Pgrid_Model_Mysql4_Column_Collection
     */
    protected function _getCollection($columnType, $groupId = null)
    {
        $groupId = $groupId ? $groupId : Mage::helper('ampgrid')->getSelectedGroupId();
        $collection = $this->getCollection()->addFieldToFilter('column_type', $columnType);
        $collection->getSelect()->joinLeft(
            array('gc' => $collection->getTable('grid_group_column')),
            sprintf('main_table.entity_id = gc.column_id AND gc.group_id = %d', $groupId),
            "gc.*, IF(gc.column_id IS NULL, main_table.visible, gc.is_visible) as visibility"
        );
        return $collection;

    }

    /**
     * @return bool
     */
    public function isEditable()
    {
        $isEditable = $this->getIsEditable();
        if(!$this->getEditable()) {
            $isEditable = false;
        } elseif(!$this->getColumnId() && $this->getColumnType() != 'standard') {
            $isEditable = false;
        }
        return $isEditable;
    }

    /**
     * @return bool
     */
    public function isVisible()
    {
        return (bool)$this->getVisibility();
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->getCustomTitle()
            ? $this->getCustomTitle()
            : $this->getData('title');
    }

    /**
     * @param $columnCode
     *
     * @return Amasty_Pgrid_Model_Column
     */
    public function loadByCode($columnCode)
    {
        $collection = $this->getCollection()->addFieldToFilter('code', $columnCode);
        $collection->getSelect()->joinInner(
            array('gc' => $collection->getTable('grid_group_column')),
            sprintf('main_table.entity_id = gc.column_id AND gc.group_id = %d', Mage::helper('ampgrid')->getSelectedGroupId())
        );
        return $collection->getFirstItem();
    }
}