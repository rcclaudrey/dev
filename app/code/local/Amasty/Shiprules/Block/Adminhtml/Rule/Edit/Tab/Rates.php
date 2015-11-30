<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Shiprules
 */ 
class Amasty_Shiprules_Block_Adminhtml_Rule_Edit_Tab_Rates extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        
        /* @var $hlp Amasty_Shiprules_Helper_Data */
        $hlp = Mage::helper('amshiprules');
        
        $fldRate = $form->addFieldset('rate', array('legend'=> $hlp->__('Rates')));
        $fldRate->addField('calc', 'select', array(
            'label'     => $hlp->__('Calculation'),
            'name'      => 'calc',
            'options'   => $hlp->getCalculations(),
        ));        
        $fldRate->addField('rate_base', 'text', array(
            'label'     => $hlp->__('Base Rate for the Order'),
            'name'      => 'rate_base',
        ));
        $fldRate->addField('rate_fixed', 'text', array(
            'label'     => $hlp->__('Fixed Rate per Product'),
            'name'      => 'rate_fixed',
        ));

        $fldRate->addField('weight_fixed', 'text', array(
            'label'     => $hlp->__('Rate per unit of weight'),
            'name'      => 'weight_fixed',
            'note'      => $hlp->__("Enter the surcharge or discount amount that'll be automatically multiplied by the product's weight to create a shipping rate."),
        ));
        
        $fldRate->addField('rate_percent', 'text', array(
            'label'     => $hlp->__('Percentage per Product'),
            'name'      => 'rate_percent',
            'note'      => $hlp->__('Percentage of original product cart price is taken, without discounts.'),
        ));
        
        $fldRate->addField('handling', 'text', array(
            'label'     => $hlp->__('Handling Percentage'),
            'name'      => 'handling',
            'note'      => $hlp->__('The percentage will be added or deducted from the shipping rate. If it is 10% and UPS Ground is $25, the total shipping cost will be $27.5'),
        ));

        $fldRate->addField('rate_min', 'text', array(
            'label'     => $hlp->__('Minimal rate change'),
            'name'      => 'rate_min',
            'note'      => $hlp->__('This is the minimal amount, which will be added or deducted by this rule.'),
        ));

        $fldRate->addField('rate_max', 'text', array(
            'label'     => $hlp->__('Maximal rate change'),
            'name'      => 'rate_max',
			'note'      => $hlp->__('This is the maximum amount, which will be added or deducted by this rule.'),
        ));

        $fldRate->addField('ship_min', 'text', array(
            'label'     => $hlp->__('Minimal rate'),
            'name'      => 'ship_min',
            'note'      => $hlp->__('Minimal total rate after the rule is applied.'),
        ));

        $fldRate->addField('ship_max', 'text', array(
            'label'     => $hlp->__('Maximal total rate'),
            'name'      => 'ship_max',
            'note'      => $hlp->__('Maximal total rate after the rule is applied.'),
        ));

        
        //set form values
        $form->setValues(Mage::registry('amshiprules_rule')->getData()); 
        
        return parent::_prepareForm();
    }
}