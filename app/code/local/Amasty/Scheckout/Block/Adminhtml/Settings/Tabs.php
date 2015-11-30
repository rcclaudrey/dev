<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Scheckout
 */
class Amasty_Scheckout_Block_Adminhtml_Settings_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('amscheckoutsettings_tabs');
        $this->setDestElementId('checkoutFieldsForm');
        $this->setTitle(Mage::helper('amscheckout')->__('Single Step Checkout Configuration'));
    }
    
    protected function _beforeToHtml()
    {
        $store_id = $this->getData("store_id");
        
        $areasBlockHtml = $this->getLayout()->createBlock('amscheckout/adminhtml_settings_tab_areas')
                ->setData('store_id', $store_id)
                ->toHtml();
        
        $this->addTab('areas_section', array(
            'label'     => Mage::helper('amscheckout')->__('Labels and Layout'),
            'title'     => Mage::helper('amscheckout')->__('Labels and Layout'),
            'content'   => $areasBlockHtml,
        ));
        
        $fieldsBlockHtml = $this->getLayout()->createBlock('amscheckout/adminhtml_settings_tab_fields')
                ->setData('store_id', $store_id)
                ->toHtml();
        
        $this->addTab('fields_section', array(
            'label'     => Mage::helper('amscheckout')->__('Fields'),
            'title'     => Mage::helper('amscheckout')->__('Fields'),
            'content'   => $fieldsBlockHtml
        ));

        if (!Mage::helper("amscheckout")->isAmastyGeoipInstalled()){
            $geoipBlockHtml = $this->getLayout()->createBlock('amscheckout/adminhtml_settings_tab_geoip')
                    ->toHtml();

            $this->addTab('geoip_section', array(
                'label'     => Mage::helper('amscheckout')->__('Geo IP'),
                'title'     => Mage::helper('amscheckout')->__('Geo IP'),
                'content'   => $geoipBlockHtml
            ));
        }
        

        $this->setActiveTab($this->getData("active_tab"));
        
        return parent::_beforeToHtml();
    }
}
?>