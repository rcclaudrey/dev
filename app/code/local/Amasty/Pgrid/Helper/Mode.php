<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Pgrid
 */
class Amasty_Pgrid_Helper_Mode extends Mage_Core_Helper_Abstract
{
    public function isMulti()
    {
        return ('multi' == Mage::getStoreConfig('ampgrid/editing/mode'));
    }
}