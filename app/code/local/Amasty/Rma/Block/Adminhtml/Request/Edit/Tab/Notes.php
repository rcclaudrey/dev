<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Rma
 */ 
class Amasty_Rma_Block_Adminhtml_Request_Edit_Tab_Notes extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        
        $hlp = Mage::helper('amrma');
    
        $fldInfo = $form->addFieldset('notes_area', array('legend'=> $hlp->__('Notes')));
        
        $fldInfo->addField('notes', 'textarea', array(
            'label'     => $hlp->__('Notes'),
            'name'      => 'notes',
        ));
        
        //set form values
        $form->setValues($this->getModel()); 
        
        return parent::_prepareForm();
    }
}
?>