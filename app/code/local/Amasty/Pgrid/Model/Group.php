<?php
 /**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Pgrid
 */

/**
 * Class Amasty_Pgrid_Model_Group
 *
 * @method setIsDefault
 * @method getIsDefault
 */
class Amasty_Pgrid_Model_Group extends Mage_Core_Model_Abstract
{

    public function _construct()
    {
        $this->_init('ampgrid/group');
    }

    /**
     * Flag for check categories
     * @return string
     */
    public function getCategoriesKey()
    {
        return 'category';
    }

    public function loadActiveGroup($attributesKey = '')
    {
        $groupId = Mage::helper('ampgrid')->getSelectedGroupId($attributesKey);
        return $this->load($groupId);
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        $coll = Mage::getModel('ampgrid/groupattribute')
            ->getCollection()
            ->addFieldToFilter('group_id', $this->getId())
            ->getColumnValues('attribute_id');
        return $coll;
    }
}
