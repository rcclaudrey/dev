<?php
/**
 * Celebros Qwiser - Magento Extension
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish correct extension functionality.
 * If you wish to customize it, please contact Celebros.
 *
 * @category    Celebros
 * @package     Celebros_Celexport
 */
class Celebros_Celexport_Model_Mysql4_Cache extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the <module>_id refers to the key field in your database table.
        $this->_init('celexport/cache', 'cache_id');
    }
}