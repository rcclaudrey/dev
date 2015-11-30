<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Rma
 */ 
class Amasty_Rma_Block_Adminhtml_Status_Edit_Tab_Templates
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
        return Mage::helper('amrma')->__('Templates');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return Mage::helper('amrma')->__('Templates');
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
        $options = $this->_getEmailTemplatesOptions();
        
        $status = Mage::registry('amrma_status');
        
        $templates = $status->getStoreTemplates();
        
        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('status_');

        $fieldset = $form->addFieldset('store_templates_fieldset', array(
            'legend'       => Mage::helper('amrma')->__('Store View Specific Templates'),
            'table_class'  => 'form-list stores-tree',
        ));
        $renderer = $this->getLayout()->createBlock('adminhtml/store_switcher_form_renderer_fieldset');
        $fieldset->setRenderer($renderer);

        foreach (Mage::app()->getWebsites() as $website) {
            $fieldset->addField("w_{$website->getId()}_template", 'note', array(
                'label'    => $website->getName(),
                'fieldset_html_class' => 'website',
            ));
            foreach ($website->getGroups() as $group) {
                $stores = $group->getStores();
                if (count($stores) == 0) {
                    continue;
                }
                $fieldset->addField("sg_{$group->getId()}_template", 'note', array(
                    'label'    => $group->getName(),
                    'fieldset_html_class' => 'store-group',
                ));
                foreach ($stores as $store) {
                    $fieldset->addField("s_{$store->getId()}", 'select', array(
                        'name'      => 'store_templates['.$store->getId().']',
                        'required'  => false,
                        'label'     => $store->getName(),
                        'value'     => isset($templates[$store->getId()]) ? $templates[$store->getId()] : '',
                        'fieldset_html_class' => 'store',
                        'options'   => $options,
                    ));
                }
            }
        }


        $this->setForm($form);
        
        return parent::_prepareForm();
    }
    
    protected function _getEmailTemplatesOptions(){
        $ret = array();
        
        $hlp = Mage::helper('amrma');
        
        $options = $hlp->getEmailTemplatesOptions();
        
        array_unshift(
            $options,
            array(
                'value'=> "",
                'label' => ""
            )
        );
        
        foreach($options as $option){
            $ret[$option["value"]] = $option["label"];
        }
        return $ret;
    }
}
