<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Acart
 */
class Amasty_Acart_Block_Adminhtml_Queue_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareLayout() {
        parent::_prepareLayout();
        if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        }
    }

    function _prepareForm(){
        $form = new Varien_Data_Form(array(
            'id'     => 'edit_form', 
            'action' => $this->getUrl('*/*/save', array('id' => $this->getParentBlock()->getModel()->getId())),
            'method' => 'post'
        ));
        $this->setForm($form);
        
        /* @var $hlp Amasty_Acart_Helper_Data */
        $hlp = Mage::helper('amacart');
    
        $fldInfo = $form->addFieldset('general', array(
            'legend'=> $hlp->__('General'), 
            'class' => 'fieldset-wide',
            
        ));
        
        $fldInfo->addField('email', 'text', array(
            'label'     => $hlp->__('Email'),
            'required'  => true,
            'name'      => 'email',
        ));
        
//        $fldInfo->addField('scheduled_at', 'text', array(
//            'label'     => $hlp->__('Scheduled At'),
//            'required'  => true,
//            'name'      => 'scheduled_at',
//        ));
        
        $fldInfo->addField('subject', 'text', array(
            'label'     => $hlp->__('Subject'),
            'required'  => true,
            'name'      => 'subject',
        ));
        
        
        $fldInfo->addField('body', 'editor', array(
            'label'     => $hlp->__('Body'),
            'required'  => true,
            'name'      => 'body',
            'config'    => Mage::getSingleton('cms/wysiwyg_config')->getConfig(),
            'wysiwyg'   => true,
            'style'     => 'height:24em',
    
        ));
        
        
        $form->setUseContainer(true);
        //set form values
        $form->setValues($this->getParentBlock()->getModel());
        return parent::_prepareForm();
    }
}