<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Shiprules
 */ 
class Amasty_Shiprules_Block_Adminhtml_Rule_Edit_Tab_Products
    extends Mage_Adminhtml_Block_Widget_Form
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    /**
     * Prepare content for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return Mage::helper('salesrule')->__('Products');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return Mage::helper('salesrule')->__('Products');
    }

    /**
     * Returns status flag about this tab can be showen or not
     *
     * @return true
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Returns status flag about this tab hidden or not
     *
     * @return true
     */
    public function isHidden()
    {
        return false;
    }

    protected function _prepareForm()
    {
        $model = Mage::registry('amshiprules_rule');
        $hlp = Mage::helper('amshiprules');

        $form = new Varien_Data_Form();
        
        $renderer = Mage::getBlockSingleton('adminhtml/widget_form_renderer_fieldset')
            ->setTemplate('promo/fieldset.phtml')
            ->setNewChildUrl($this->getUrl('*/adminhtml_rule/newActionHtml/form/rule_actions_fieldset'));

        $fieldset = $form->addFieldset('rule_actions_fieldset', array(
            'legend'=> $hlp->__('Select products or leave blank for all products')
        ))->setRenderer($renderer);

        $fieldset->addField('actions', 'text', array(
            'name' => 'actions',
            'label' => Mage::helper('salesrule')->__('Conditions'),
            'title' => Mage::helper('salesrule')->__('Conditions'),
        ))->setRule($model)->setRenderer(Mage::getBlockSingleton('rule/actions'));
        
        $fldFree = $form->addFieldset('free', array('legend'=> $hlp->__('Free Shipping')));
        $fldFree->addField('ignore_promo', 'select', array(
            'label'     => $hlp->__('Ignore Free Shipping Promo'),
            'name'      => 'ignore_promo',
            'options'   => array(
                '0' => $hlp->__('No'),
                '1' => $hlp->__('Yes'),
            ),
            'note'      => $hlp->__('If the option is set to `No`, totals below will be applied only to items with non-free shipping.'),
        ));        

        $fldTotals = $form->addFieldset('totals', array('legend'=> $hlp->__('Totals for selected products, excluding items shipped for free.')));
        $fldTotals->addField('weight_from', 'text', array(
            'label'     => $hlp->__('Weight From'),
            'name'      => 'weight_from',
        ));
        $fldTotals->addField('weight_to', 'text', array(
            'label'     => $hlp->__('Weight To'),
            'name'      => 'weight_to',
        ));
        
        $fldTotals->addField('qty_from', 'text', array(
            'label'     => $hlp->__('Qty From'),
            'name'      => 'qty_from',
        ));
        $fldTotals->addField('qty_to', 'text', array(
            'label'     => $hlp->__('Qty To'),
            'name'      => 'qty_to',
        ));  
        
        $fldTotals->addField('price_from', 'text', array(
            'label'     => $hlp->__('Price From'),
            'name'      => 'price_from',
            'note'      => $hlp->__('Original product cart price, without discounts.'),
        ));
        $fldTotals->addField('price_to', 'text', array(
            'label'     => $hlp->__('Price To'),
            'name'      => 'price_to',
            'note'      => $hlp->__('Original product cart price, without discounts.'),
        ));               

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
