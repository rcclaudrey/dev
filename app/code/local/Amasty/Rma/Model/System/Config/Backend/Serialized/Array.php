<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Rma
 */ 
class Amasty_Rma_Model_System_Config_Backend_Serialized_Array extends Mage_Adminhtml_Model_System_Config_Backend_Serialized_Array
{
    protected function _afterLoad()
    {
        
        if ($this->getValue() instanceof Mage_Core_Model_Config_Element){
            $arrValue = (array)$this->getValue();
            
            $values = array();
            
            if (isset($arrValue['type']) && $arrValue['type'] == 'default'){
                $values = (array)$arrValue['values'];
            } else {
                $values = @unserialize($arrValue[0]);
            }
            
            $value = array();
            foreach($values as $v){
                $value [uniqid('amrma_')] = array(
                    'value' => $v
                );
            }
            
            $this->setValue($value);
        
        } else if (!is_array($this->getValue())) {
            $value = $this->getValue();
            
            if (empty($value)){
                $value = false;
            } else {
                $value = unserialize($value);
            }
            
            $this->setValue($value);
        }
    }

    protected function _beforeSave()
    {
        parent::_beforeSave();
        
        if (is_array($this->getValue())) {
            
            $this->setValue(serialize($this->getValue()));
        }
        
    }
}

?>