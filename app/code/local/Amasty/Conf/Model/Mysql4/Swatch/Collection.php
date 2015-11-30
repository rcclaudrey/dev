<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Conf
 */
 

class Amasty_Conf_Model_Mysql4_Swatch_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {

    protected $colorTable = 'amasty_amconf_swatch';

    protected function _construct() {
        $this->_init('amconf/swatch');
        $this->colorTable = $this->getTable('swatch');
    }
}
?>