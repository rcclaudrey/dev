<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Shiprules
 */  
class Amasty_Shiprules_Block_Adminhtml_Rule_Edit_Tab_Stores extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        
        /* @var $hlp Amasty_Shiprules_Helper_Data */
        $hlp = Mage::helper('amshiprules');
    
        $fldStore = $form->addFieldset('apply_in', array('legend'=> $hlp->__('Apply In')));
        $fldStore->addField('stores', 'multiselect', array(
            'label'     => $hlp->__('Stores'),
            'name'      => 'stores[]',
            'values'    => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(),
            'note'      => $hlp->__('Leave empty to apply the rule to any store'), 
        ));  

        $fldCust = $form->addFieldset('apply_for', array('legend'=> $hlp->__('Apply For')));
        $fldCust->addField('cust_groups', 'multiselect', array(
            'name'      => 'cust_groups[]',
            'label'     => $hlp->__('Customer Groups'),
            'values'    => $hlp->getAllGroups(),
            'note'      => $hlp->__('Leave empty to apply the rule to any group'),
        ));              
        
        //set form values
        $form->setValues(Mage::registry('amshiprules_rule')->getData()); 
        
        return parent::_prepareForm();
    }
}