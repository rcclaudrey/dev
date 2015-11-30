<?php
 /**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Pgrid
 */

class Amasty_Pgrid_Model_Mysql4_Groupattribute extends Mage_Core_Model_Mysql4_Abstract
{
    protected $_notEditableAttributes
        = array('tier_price', 'gallery', 'media_gallery', 'recurring_profile',
                'group_price');

    public function getNotEditableAttributes()
    {
        return $this->_notEditableAttributes;
    }

    public function _construct()
    {
        $this->_init('ampgrid/grid_group_attribute', 'group_attribute_id');
    }

    public function insert($data)
    {
        $this->_getWriteAdapter()->insertMultiple($this->getMainTable(), $data);
        return $this;
    }
}