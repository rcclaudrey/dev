<?php
 /**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Pgrid
 */

class Amasty_Pgrid_Model_Groupattribute extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        $this->_init('ampgrid/groupattribute');
    }

    /**
     * @param int $groupId
     * @return Amasty_Pgrid_Model_Mysql4_GroupAttribute_Collection
     */
    public function getCollectionAttribute($groupId)
    {
        $collection = $this->getCollection()->getCollectionAttribute($groupId);
        return $collection;
    }

    /**
     * @return bool
     */
    public function isEditable()
    {
        return (bool)($this->getIsEditable());
    }

    /**
     * @return bool
     */
    public function isVisible()
    {
        return (bool)((!$this->getColumnId() && $this->getColumnType() == 'standard') || $this->getIsVisible());
    }

    public function insert($data)
    {
        $this->getResource()->insert($data);
        return $this;
    }
}