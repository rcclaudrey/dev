<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Acart
 */ 
class Amasty_Acart_Model_Canceled extends Mage_Core_Model_Abstract
{
    const REASON_ELAPSED = 'elapsed';
    const REASON_BOUGHT = 'bought';
    const REASON_LINK = 'link';
    const REASON_BALCKLIST = 'blacklist';
    const REASON_ADMIN = 'admin';
    const REASON_UPDATED = 'updated';
    
    public function _construct()
    {
        parent::_construct();
        $this->_init('amacart/canceled');
    }
}
?>