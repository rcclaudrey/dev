<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Shiprules
 */ 
class Amasty_Shiprules_Block_Adminhtml_Rule_Edit_Tab_General extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        
        /* @var $hlp Amasty_Shiprules_Helper_Data */
        $hlp = Mage::helper('amshiprules');
    
        $fldInfo = $form->addFieldset('general', array('legend'=> $hlp->__('General')));
        $fldInfo->addField('name', 'text', array(
            'label'     => $hlp->__('Name'),
            'required'  => true,
            'name'      => 'name',
        ));
        $fldInfo->addField('is_active', 'select', array(
            'label'     => Mage::helper('salesrule')->__('Status'),
            'name'      => 'is_active',
            'options'    => $hlp->getStatuses(),
        ));  
            
        $fldInfo->addField('carriers', 'multiselect', array(
            'label'     => $hlp->__('Shipping Carriers'),
            'name'      => 'carriers[]',
            'values'    => $hlp->getAllCarriers(),
            'required'  => true,
        ));            
        
        $fldInfo->addField('methods', 'textarea', array(
            'label'     => $hlp->__('Shipping Methods'),
            'name'      => 'methods',
            'note'      => $hlp->__('One method name per line, e.g Next Day Air. Leave empty for all methods.'), 
        ));
        
        $fldInfo->addField('coupon', 'text', array(
            'label'     => Mage::helper('salesrule')->__('Coupon Code'), 
            'name'      => 'coupon',
			'note'      => $hlp->__('Apply this shipping rule when specified coupon is provided. You can configure coupon in promotions / shopping cart rules. Useful when you have ONE coupon only.'),
        ));    
        
        $fldInfo->addField('discount_id', 'select', array(
            'label'     => $hlp->__('Shopping Cart Rule (discount)'),
            'name'      => 'discount_id',
            'values'    => $hlp->getAllRules(),
			'note'      => $hlp->__('Apply this rule with ANY coupon from specified discount rule. See promotions / shopping cart rules. Useful when you have MULTIPLE coupons in one rule.'),
        ));         
		
        $fldInfo->addField('days', 'multiselect', array(
            'label'     => $hlp->__('Days of the week'),
            'name'      => 'days[]',
            'values'    => $hlp->getAllDays(),
            'note'      => $hlp->__('Apply the rules for selected days of week only. Set empty for all days.'),
        ));        

        
        $fldInfo->addField('pos', 'text', array(
            'label'     => Mage::helper('salesrule')->__('Priority'), 
            'name'      => 'pos',
            'note'      => $hlp->__('If a product matches several rules, the first rule will be applied only.'),
        ));
        
        //set form values
        $form->setValues(Mage::registry('amshiprules_rule')->getData()); 
        
        return parent::_prepareForm();
    }
}