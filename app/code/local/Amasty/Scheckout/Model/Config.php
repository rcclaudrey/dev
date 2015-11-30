<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Scheckout
 */
class Amasty_Scheckout_Model_Config extends Mage_Core_Model_Abstract
{
    protected $_layoutTypes = array(
        'one_column' => 'One Column',
        'two_columns' => 'Two Columns',
        'three_columns' => 'Three Columns'
    );
    
    protected $_defaultLayoutType = 'three_columns';
    protected $_layoutTypeKey = 'amscheckout/layout/type';
    
    
    public function _construct()
    {
        $this->_init('amscheckout/config');
    }
    
    function getLayoutTypes(){
        return $this->_layoutTypes;
    }
    
    function getLayoutType($storeId = NULL, $useMainStoreAsDefault = TRUE){
        $ret = $this;
        
        $configCollection = Mage::getResourceModel('amscheckout/config_collection');
        $configCollection->getSelect()->where(
            $configCollection->getConnection()->quoteInto(
                    'store_id = ?', 
                    $storeId
            )
        );

        $configCollection->getSelect()->where(
            $configCollection->getConnection()->quoteInto(
                    'variable = ?', 
                    $this->_layoutTypeKey
            )
        );
        
       
        
        $configs = $configCollection->getItems();
        $configs = array_values($configs);
        
        if (count($configs) > 0)
        {
            $ret = $configs[0];
        } 
        
        $mode = $ret->value;
        
        if ($ret && empty($mode))
        {
            
            if ($storeId != NULL && $useMainStoreAsDefault){
                
                $ret = $this->getLayoutType(NULL);
            } else {
                $ret->store_id = $storeId;
                $ret->variable = $this->_layoutTypeKey;
                $ret->value = $this->_defaultLayoutType;
            }
        }
        
        return $ret;
        
    }
    
    function setLayoutType($storeId, $layoutType){
        
        $config = $this->getLayoutType($storeId, FALSE);
        
        $config->value = $layoutType;
        $config->save();
        
        return $config;
    }
}
?>