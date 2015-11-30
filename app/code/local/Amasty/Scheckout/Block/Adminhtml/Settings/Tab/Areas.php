<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Scheckout
 */
class Amasty_Scheckout_Block_Adminhtml_Settings_Tab_Areas extends Mage_Adminhtml_Block_Template implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    protected function _construct()
    {
        $this->setTemplate('amscheckout/areas.phtml');
    }
    
    public function getTabLabel()
    {
        return Mage::helper('amscheckout')->__('Areas Configuration');
    }
    
    public function getTabTitle()
    {
        return Mage::helper('amscheckout')->__('Areas Configuration');
    }
    
    public function canShowTab()
    {
        return true;
    }
    
    public function isHidden()
    {
        return false;
    }
    
    public function getAreas(){
        $storeId = $this->getData("store_id");
        return Mage::getModel("amscheckout/area")->getAreas($storeId);
    }
    
    public function getLayoutTypes(){
        $ret = array();
        
        $storeId = $this->getData("store_id");
        $config = Mage::getModel("amscheckout/config");
        
        $layoutTypes = $config->getLayoutTypes();
        $layoutType = $config->getLayoutType($storeId)->value;
        
        foreach($layoutTypes as $name => $value){
            $ret[] = array(
                'name' => $name,
                'value' => $value,
                'active' => $layoutType == $name
            );
        }
        
        return $ret;
        
    }
}
?>