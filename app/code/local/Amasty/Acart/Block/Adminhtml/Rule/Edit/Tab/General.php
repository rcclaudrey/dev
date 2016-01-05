<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Acart
 */ 
class Amasty_Acart_Block_Adminhtml_Rule_Edit_Tab_General extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        
        /* @var $hlp Amasty_Acart_Helper_Data */
        $hlp = Mage::helper('amacart');
    
        $fldInfo = $form->addFieldset('general', array('legend'=> $hlp->__('General')));
        
        $fldInfo->addField('name', 'text', array(
            'label'     => $hlp->__('Name'),
            'required'  => true,
            'name'      => 'name',
        ));
        
        $fldInfo->addField('is_active', 'select', array(
            'label'     => $hlp->__('Status'),
            'name'      => 'is_active',
            'options'    => $hlp->getStatuses(),
        ));
        
        $fldInfo->addField('priority', 'text', array(
            'label'     => $hlp->__('Priority'),
            'name'      => 'priority',
        ));
        
        $fldInfo->addField('cancel_rule', 'select', array(
            'label'     => $hlp->__('Cancel Condition'),
            'name'      => 'cancel_rule',
            'options'    => $hlp->getCancelRules(),
        ));
        
        
         
        //set form values
        $form->setValues($this->getModel()); 
        
        return parent::_prepareForm();
    }
}