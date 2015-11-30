<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Rma
 */ 
class Amasty_Rma_Model_Mysql4_Request extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        $this->_init('amrma/request', 'request_id');
    }
        
    public function getStatus($statusId){
        return Mage::getModel('amrma/status')->load($statusId);
    }
}
?>