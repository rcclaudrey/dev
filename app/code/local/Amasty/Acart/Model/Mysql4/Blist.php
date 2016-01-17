<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Acart
 */ 
class Amasty_Acart_Model_Mysql4_Blist extends Mage_Core_Model_Mysql4_Abstract
{
    protected $_warnings = array();
    
    public function _construct()
    {    
        $this->_init('amacart/blacklist', 'blacklist_id');
    }
    
    public function uploadAndImport(Varien_Object $object)
    {
        $hlr = Mage::helper("amacart");
        
        if (empty($_FILES['groups']['tmp_name']['import']['fields']['blacklist']['value'])) {
            return $this;
        }

        $csvFile = $_FILES['groups']['tmp_name']['import']['fields']['blacklist']['value'];
        

        $io     = new Varien_Io_File();
        $info   = pathinfo($csvFile);
        $io->open(array('path' => $info['dirname']));
        $io->streamOpen($info['basename'], 'r');
        $emails = array();
        
        while (($csvLine = $io->streamReadCsv()) !== FALSE) {
            foreach($csvLine as $email){
                
                if (!Zend_Validate::is($email, 'NotEmpty')) {
                    
                } else if (!Zend_Validate::is($email, 'EmailAddress')) {
                    $this->_warnings[] = $email . " " . $hlr->__("not valid email");
                } else {
                    $emails[] = array(
                        "email" => $email,
                        'created_at' => date("Y-m-d H:i:s", time()),
                    );
                }
                
                if (count($emails) == 100){
                    $this->saveImportData($emails);
                    $emails = array();
                }
            }
        }
        
        $this->saveImportData($emails);
        
        
        
        foreach(array_slice($this->_warnings, 0, 10) as $warning){
            Mage::getSingleton('adminhtml/session')->addWarning($warning); 
        }
        
        Mage::getSingleton('core/session')->addSuccess($hlr->__("Import completed")); 

    }
    
    public function saveImportData($emails)
    {
        $this->_getWriteAdapter()->insertOnDuplicate($this->getMainTable(), $emails, array("email"));
    }
    
}