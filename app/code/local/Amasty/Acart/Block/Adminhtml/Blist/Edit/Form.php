<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Acart
 */

/**
 * @author Amasty
 */ 
class Amasty_Acart_Block_Adminhtml_Blist_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
    $form = new Varien_Data_Form(array(
      'id' => 'edit_form',
      'action' => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
      'method' => 'post'));
    
    $form->setUseContainer(true);
    $this->setForm($form);
    $hlp = Mage::helper('amacart');
    
    $fldInfo = $form->addFieldset('amacart_info', array('legend'=> $hlp->__('Recipient')));
    
    $fldInfo->addField('email', 'text', array(
      'label'     => $hlp->__('Email'),
      'class'     => 'required-entry validate-email',
      'required'  => true,
      'name'      => 'email',
    ));    
    
    //set form values
    $data = Mage::getSingleton('adminhtml/session')->getFormData();
    $model = Mage::registry('amacart_blist');
    if ($data) {
        $form->setValues($data);
        Mage::getSingleton('adminhtml/session')->setFormData(null);
    }
    elseif ($model) {
        $form->setValues($model->getData());
    } 
    
    return parent::_prepareForm();
  }
}