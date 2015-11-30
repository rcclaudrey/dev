<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Scheckout
 */
class Amasty_Scheckout_Adminhtml_Amscheckout_ImportController extends Mage_Adminhtml_Controller_Action
{
    protected $_geoipFiles = array(
        'country' => 'GeoIPCountryWhois.csv', 
        'block' => 'GeoLiteCity-Blocks.csv',
        'location' => 'GeoLiteCity-Location.csv'
    );
    
     protected $_geoipIgnoredLines = array(
        'country' => 0, 
        'block' => 2,
        'location' => 2
    );
    
    public function startAction()
    {
        $result = array();
        
        try{
            $type = $this->getRequest()->getParam('type');
    
            $dir = Mage::getModuleDir('sql', 'Amasty_Scheckout');
            $filePath = $dir.'/geoip/'.$this->_geoipFiles[$type];

            $ret = Mage::getModel('amscheckout/import')->startProcess($type, $filePath, $this->_geoipIgnoredLines[$type]);
            
            $result['position'] = ceil($ret['current_row'] / $ret['rows_count'] * 100);
            $result['status'] = 'started';
            $result['file'] = $this->_geoipFiles[$type];
            
            
        } catch(Exception $e){
            $result['error'] = $e->getMessage();
        }
        
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        
    }
    
    public function processAction(){
        $result = array();
        
        try{
            $type = $this->getRequest()->getParam('type');

            $dir = Mage::getModuleDir('sql', 'Amasty_Scheckout');
            $filePath = $dir.'/geoip/'.$this->_geoipFiles[$type];

            $ret = Mage::getModel('amscheckout/import')->doProcess($type, $filePath);
            
            $result['status'] = 'processing';
            $result['position'] = ceil($ret['current_row'] / $ret['rows_count'] * 100);
            
        } catch(Exception $e){
            $result['error'] = $e->getMessage();
        }
        
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }
    
    public function commitAction(){
        
        $result = array();
        
        try{
            $type = $this->getRequest()->getParam('type');
    
            $import = Mage::getModel('amscheckout/import');
            
            $import->commitProcess($type); 
            
            $result['status'] = 'done';
            
            $result['full_import_done'] = Mage::getModel('amscheckout/import')->isDone() ? "1" : "0";
            
        }  catch(Exception $e){
            $result['error'] = $e->getMessage();
        }
        
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('system/amscheckout_adminsetting');
    }
}
?>