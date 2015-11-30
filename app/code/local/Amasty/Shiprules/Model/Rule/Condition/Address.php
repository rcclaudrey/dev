<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Shiprules
 */
class Amasty_Shiprules_Model_Rule_Condition_Address extends Mage_Rule_Model_Condition_Abstract
{
    public function loadAttributeOptions()
    {
        $attributes = array(
            'package_value'    => Mage::helper('salesrule')->__('Subtotal'),
            'package_value_with_discount'   => Mage::helper('salesrule')->__('Subtotal with discount'),
            'package_qty'      => Mage::helper('salesrule')->__('Total Items Quantity'),
            'package_weight'   => Mage::helper('salesrule')->__('Total Weight'),
            'dest_postcode'    => Mage::helper('salesrule')->__('Shipping Postcode'),
            'dest_region_id'   => Mage::helper('salesrule')->__('Shipping State/Province'),
            'dest_country_id'  => Mage::helper('salesrule')->__('Shipping Country'),
            'dest_city'        => Mage::helper('salesrule')->__('Shipping City'),
            'dest_street'      => Mage::helper('salesrule')->__('Shipping Address Line'),
        );

        $this->setAttributeOption($attributes);

        return $this;
    }

    public function getAttributeElement()
    {
        $element = parent::getAttributeElement();
        $element->setShowAsText(true);
        return $element;
    }

    public function getInputType()
    {
        switch ($this->getAttribute()) {
            case 'package_value': case 'package_weight': case 'package_qty':
                return 'numeric';

            case 'dest_country_id': case 'dest_region_id':
                return 'select';
        }
        return 'string';
    }

    public function getValueElementType()
    {
        switch ($this->getAttribute()) {
            case 'dest_country_id': case 'dest_region_id':
                return 'select';
        }
        return 'text';
    }

    public function getValueSelectOptions()
    {
        if (!$this->hasData('value_select_options')) {
            switch ($this->getAttribute()) {
                case 'dest_country_id':
                    $options = Mage::getModel('adminhtml/system_config_source_country')
                        ->toOptionArray();
                    break;

                case 'dest_region_id':
                    $options = Mage::getModel('adminhtml/system_config_source_allregion')
                        ->toOptionArray();
                    break;

                default:
                    $options = array();
            }
            $this->setData('value_select_options', $options);
        }
        return $this->getData('value_select_options');
    }
    
    public function getOperatorSelectOptions()
    {
        $operators = $this->getOperatorOption();
        if ($this->getAttribute() == 'dest_street') {
             $operators = array(
                '{}'  => Mage::helper('rule')->__('contains'),
                '!{}' => Mage::helper('rule')->__('does not contain'), 
                '{%'  => Mage::helper('rule')->__('starts from'),           
                '%}'  => Mage::helper('rule')->__('ends with'),           
             );
        }
        
        $type = $this->getInputType();
        $opt = array();
        $operatorByType = $this->getOperatorByInputType();
        foreach ($operators as $k => $v) {
            if (!$operatorByType || in_array($k, $operatorByType[$type])) {
                $opt[] = array('value' => $k, 'label' => $v);
            }
        }
        return $opt;
    }    
    
    public function getDefaultOperatorInputByType()
    {
        $op = parent::getDefaultOperatorInputByType();
        $op['string'][] = '{%';
        $op['string'][] = '%}';
        return $op;
    }

    public function getDefaultOperatorOptions()
    {
        $op = parent::getDefaultOperatorOptions();
        $op['{%'] = Mage::helper('rule')->__('starts from');
        $op['%}'] = Mage::helper('rule')->__('ends with');        

        return $op;
    }    
    
    public function validateAttribute($validatedValue)
    {
        if (is_object($validatedValue)) {
            return false;
        }
        
        if (is_string($validatedValue)){
            $validatedValue = strtoupper($validatedValue);
        }

        /**
         * Condition attribute value
         */
        $value = $this->getValueParsed();
        if (is_string($value)){
            $value = strtoupper($value);
        }

        /**
         * Comparison operator
         */
        $op = $this->getOperatorForValidate();

        // if operator requires array and it is not, or on opposite, return false
        if ($this->_isArrayOperatorType() xor is_array($value)) {
            return false;
        }

        $result = false;
        switch ($op) {
            case '{%':
                if (!is_scalar($validatedValue)) {
                    return false;
                } else {
                    $result = substr($validatedValue,0,strlen($value)) == $value;
                }             
                break;
             case '%}':
                if (!is_scalar($validatedValue)) {
                    return false;
                } else {
                    $result = substr($validatedValue,-strlen($value)) == $value;
                }               
                break;  
             default:
                return parent::validateAttribute($validatedValue);
                break;        
        }
        return $result;        
                
    } 

    /**
     * Check if value should be array
     *
     * Depends on operator input type
     *
     * @return bool
     */
    protected function _isArrayOperatorType()
    {
        $ret = false;
        if (method_exists($this, 'isArrayOperatorType')){
            $ret = $this->isArrayOperatorType();
        } else {
            $op  = $this->getOperator();
            $ret = ($op === '()' || $op === '!()');
        }
         
        return $ret;
    }       
}