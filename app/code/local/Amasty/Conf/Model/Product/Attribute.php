<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Conf
 */
class Amasty_Conf_Model_Product_Attribute extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('amconf/product_attribute');
    }
}
