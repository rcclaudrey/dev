<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Scheckout
 */
class Amasty_Scheckout_Block_Adminhtml_Settings_Tab_Geoip extends Mage_Adminhtml_Block_Widget_Form
{
    protected $_geoipRequiredFiles = array(
        'GeoIPCountryWhois.csv', 'GeoLiteCity-Blocks.csv',
        'GeoLiteCity-Location.csv'
    );
    
    protected function importAvailable(){
        $ret = TRUE;
        
        $dir = Mage::getModuleDir('sql', 'Amasty_Scheckout');
        
        foreach($this->_geoipRequiredFiles as $file){
            if (!file_exists($dir.'/geoip/'.$file)){
                $ret = FALSE;
                break;
            }
        }
        
        return $ret;
        
    }
    
    protected function _prepareForm()
    {
        
        
        $importAvailable = $this->importAvailable();
        
        
        //create form structure
        $form = new Varien_Data_Form();
        $this->setForm($form);
        
        $hlp = Mage::helper('amscheckout');
        
        $fldSet = $form->addFieldset('amscheckout_import', array('legend'=> $hlp->__('Geo IP Settings')));
        
        
        $fldSet->addField('useGeoip', 'select', array(
            'label'=>$hlp->__('Use Geo IP'),
            'name'=>'useGeoip',
            'disabled' => Mage::getModel('amscheckout/import')->isDone() ? '0' : '1',
            'value' => Mage::getStoreConfig('amscheckout/geoip/use') == 1 ? '1' : '0',
            'values'=>array(
                array(
                    'value'=>0,
                    'label'=>Mage::helper('amscheckout')->__('No')
                ),
                array(
                    'value'=>1,
                    'label'=>Mage::helper('amscheckout')->__('Yes')
                )
            )
        ));
        
        
        $onclick = 'var inputCaller = this;';
        
        $importTypes = array(
            'country', 'location',
            'block'
        );
        
        foreach($importTypes as $type){
            $startUrl = $this->getUrl('*/amscheckout_import/start', array(
                'type' => $type
            ));

            $processUrl = $this->getUrl('*/amscheckout_import/process', array(
                'type' => $type
            ));

            $commitUrl = $this->getUrl('*/amscheckout_import/commit', array(
                'type' => $type
            ));
            
            $onclick .= 'window.setTimeout(function(){ amImportObj.run(\''.$startUrl.'\', \''.$processUrl.'\', \''.$commitUrl.'\', inputCaller);}, 100); ';
        }
        
        
        
        $fldSet->addField('import_file', 'button', array(
            'label' => $hlp->__('Import'),
            'name' => 'import_file',
            'value' => $hlp->__('Process...'),
            'class' => 'form-button',
            'note' => $importAvailable ? '' : $hlp->__('Required files:').' '.implode(', ', $this->_geoipRequiredFiles),
            'onclick' => $onclick,
            'disabled' => $importAvailable ? false : true
        ));
        
        

        return parent::_prepareForm();
    }
}