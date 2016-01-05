<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Acart
 */ 
class Amasty_Acart_Block_Adminhtml_Rule_Edit_Tab_Analytics extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        
        /* @var $hlp Amasty_Acart_Helper_Data */
        $hlp = Mage::helper('amacart');
    
        $fldInfo = $form->addFieldset('general', array('legend'=> $hlp->__('Google Analytics')));
                
        $fldInfo->addField('utm_source', 'text', array(
            'label'     => $hlp->__('Campaign Source'),
            'name'      => 'utm_source',
            'note' => $hlp->__('<b>Required.</b> Use <b>utm_source</b> to identify a search engine, newsletter name, or other source.<br/><i>Example:</i> utm_source=google')
        ));
        
        $fldInfo->addField('utm_medium', 'text', array(
            'label'     => $hlp->__('Campaign Medium'),
            'name'      => 'utm_medium',
            'note' => $hlp->__('<b>Required.</b> Use <b>utm_medium</b> to identify a medium such as email or cost-per- click<br/><i>Example:</i> utm_medium=cpc')
        ));
        
        $fldInfo->addField('utm_term', 'text', array(
            'label'     => $hlp->__('Campaign Term'),
            'name'      => 'utm_term',
            'note' => $hlp->__('Used for paid search. Use <b>utm_term</b> to note the keywords for this ad.<br/><i>Example:</i> utm_term=running+shoes')
        ));
        
        $fldInfo->addField('utm_content', 'text', array(
            'label'     => $hlp->__('Campaign Content'),
            'name'      => 'utm_content',
            'note' => $hlp->__('Used for A/B testing and content-targeted ads. Use <b>utm_content</b> to differentiate ads or links that point to the same URL.<br/><i>Example:</i> utm_content=logolink <i>or</i> utm_content=textlink')
        ));
        
        $fldInfo->addField('utm_campaign', 'text', array(
            'label'     => $hlp->__('Campaign Name'),
            'name'      => 'utm_campaign',
            'note' => $hlp->__('Used for keyword analysis. Use <b>utm_campaign</b> to identify a specific product promotion or strategic campaign.<br/><i>Example:</i> utm_campaign=spring_sale')
        ));
        
        //set form values
        $form->setValues($this->getModel()); 
        
        return parent::_prepareForm();
    }
}