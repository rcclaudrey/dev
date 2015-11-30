<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Scheckout
 */
class Amasty_Scheckout_Adminhtml_Amscheckout_SettingsController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $activeTab = $this->getRequest()->getParam("active_tab");
        $storeId = $this->getRequest()->getParam("store");
        
        $this->loadLayout();
        
        $this->_addContent($this->getLayout()->createBlock('amscheckout/adminhtml_settings')->setData('store_id', $storeId))
                ->_addLeft(
                        $this->getLayout()->createBlock('amscheckout/adminhtml_settings_tabs')
                        ->setData('store_id', $storeId)
                        ->setData('active_tab', $activeTab)
                    )
                ->renderLayout();
    }
    
    public function saveAction(){
        
        $saveData = $this->getRequest()->getParam('saveData', array());
        $storeId = $this->getRequest()->getParam("store");
        $activeTab = $this->getRequest()->getParam("activeTab");
        $layoutMode = $this->getRequest()->getParam("layoutMode");
        $useGeoip = $this->getRequest()->getParam("useGeoip");
        
        
        Mage::getModel("amscheckout/config")->setLayoutType($storeId, $layoutMode);
        
        foreach($saveData as $key => $saveItems){
            
            $aKey = explode("_", $key);
            if (strpos($key, 'area') !== FALSE){
                $areaId = $aKey[1];
                
                $area = Mage::getModel("amscheckout/area")->load($areaId);
                
                $area->updateByFields($saveItems, $storeId);
                
            } else if (strpos($key, 'field') !== FALSE){
                $fieldId = $aKey[1];
                
                $field = Mage::getModel("amscheckout/field")->load($fieldId);
                
                $field->updateByFields($saveItems, $storeId);

            }
        }
        
        if (!empty($storeId)){
            
        } else {
            Mage::getModel("amscheckout/field")->updateDefaultOrders();
        }
        
        
        Mage::getModel('core/config')->saveConfig('amscheckout/geoip/use', $useGeoip == 1 ? 1 : 0);
                
        $backUrl = Mage::app()->getRequest()->getParam('backurl');
        if (!$backUrl)
        {
            $backUrl = $this->getUrl('*/*/index', array(
                'store' => $storeId,
                'active_tab' => $activeTab
            ));
        }
        
        $this->_getSession()->addSuccess($this->__('The fields has been saved.'));
        $this->getResponse()->setRedirect($backUrl);
        
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('system/amscheckout_adminsetting');
    }
}
?>