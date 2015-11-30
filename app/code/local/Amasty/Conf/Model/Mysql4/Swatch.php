<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Conf
 */
 

class Amasty_Conf_Model_Mysql4_Swatch extends Mage_Core_Model_Mysql4_Abstract {
    protected $_isPkAutoIncrement = false;

    protected function _construct() {
        $this->_init('amconf/swatch', 'attribute_id');
    }
}
