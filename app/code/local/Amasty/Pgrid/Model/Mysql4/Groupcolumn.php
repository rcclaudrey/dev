<?php
 /**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Pgrid
 */

class Amasty_Pgrid_Model_Mysql4_Groupcolumn extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('ampgrid/grid_group_column', 'group_column_id');
    }
}