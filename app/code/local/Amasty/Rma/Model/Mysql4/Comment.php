<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Rma
 */ 
class Amasty_Rma_Model_Mysql4_Comment extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        $this->_init('amrma/comment', 'comment_id');
    }   
}
?>