<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Scheckout
 */
class Amasty_Scheckout_Block_Adminhtml_Settings_Tab_Fields extends Mage_Adminhtml_Block_Template implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    protected function _construct()
    {
        $this->setTemplate('amscheckout/fields.phtml');
    }
    
    public function getTabLabel()
    {
        return Mage::helper('amscheckout')->__('Fields Configuration');
    }
    
    public function getTabTitle()
    {
        return Mage::helper('amscheckout')->__('Fields Configuration');
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
        $store_id = $this->getData("store_id");
        return Mage::getModel("amscheckout/area")->getAreas($store_id);
    }
    
    public function getFields(){
        $store_id = $this->getData("store_id");
        return Mage::getModel("amscheckout/field")->getFields($store_id);
    }

}
?>