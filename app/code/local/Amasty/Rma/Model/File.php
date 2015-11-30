<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Rma
 */ 
class Amasty_Rma_Model_File extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        $this->_init('amrma/file');
    }
   
    
    public function getHref(){
        return Mage::getUrl('amrmafront/customer/download', array('file' => $this->getFile()));
    }
    
    public static function getUploadPath($file)
    {
        return Mage::getBaseDir('media') . DS . 'amasty' . DS .'amrma' . DS . 'comments_upload'. DS . $file;
    }
}