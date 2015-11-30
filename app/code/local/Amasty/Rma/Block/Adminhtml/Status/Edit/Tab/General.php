<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Rma
 */ 
class Amasty_Rma_Block_Adminhtml_Status_Edit_Tab_General extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $status = Mage::registry('amrma_status');
        
        $form = new Varien_Data_Form();
        $this->setForm($form);
        
        /* @var $hlp Amasty_Rma_Helper_Data */
        $hlp = Mage::helper('amrma');
    
        $fldInfo = $form->addFieldset('general', array('legend'=> $hlp->__('General')));
        
        $labels = $status->getStoreLabels();
        $template = $status->getStoreTemplates();
        
        $fldInfo->addField('store_default_label', 'text', array(
            'name'      => 'store_labels[0]',
            'required'  => true,
            'label'     => Mage::helper('amrma')->__('Label'),
            'value'     => isset($labels[0]) ? $labels[0] : '',
        ));
        
        $fldInfo->addField('is_active', 'select', array(
            'label'     => $hlp->__('Status'),
            'name'      => 'is_active',
            'options'    => $hlp->getStatuses(),
            'value' => $this->getModel()->getIsActive()
        ));
        
        $fldInfo->addField('email_template_id', 'select', array(
            'label'     => $hlp->__('Email Template'),
            'name'      => 'store_templates[0]',
//            'required'  => true,
            'options'   => $this->_getEmailTemplatesOptions(),
            'value'     => isset($template[0]) ? $template[0] : '',
        ));

        $fldInfo->addField('order_number', 'text', array(
            'label'     => $hlp->__('Sort'),
            'name'      => 'order_number',
            'class' => 'validate-digits',
            'value' => $this->getModel()->getOrderNumber()
        ));
        
        
        
        
        
//        $form->setValues($this->getModel()); 
        
//        Mage::getModel('adminhtml/system_config_source_email_template')->toOptionArray()
                         
        //set form values
        
        
        return parent::_prepareForm();
    }
    
    protected function _getEmailTemplatesOptions(){
        $ret = array("" => "");
        
        $hlp = Mage::helper('amrma');
        
        $options = $hlp->getEmailTemplatesOptions();

        foreach($options as $option){
            $ret[$option["value"]] = $option["label"];
        }
        return $ret;
    }
}