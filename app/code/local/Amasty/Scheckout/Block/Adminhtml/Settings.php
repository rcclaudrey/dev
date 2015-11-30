<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Scheckout
 */
    class Amasty_Scheckout_Block_Adminhtml_Settings extends Mage_Adminhtml_Block_Template{
        
        protected function _construct()
        {
            $this->setTemplate('amscheckout/settings.phtml');
        }

        protected function _prepareLayout()
        {
            $this->setChild('save_button',
                $this->getLayout()->createBlock('adminhtml/widget_button')
                    ->setData(array(
                        'label' => Mage::helper('adminhtml')->__('Save'),
                        'onclick' => 'amScheckoutObj.submit()',
                        'class' => 'save'
            )));


            return parent::_prepareLayout();
        }

        protected function getHeader()
        {
            return Mage::helper('amscheckout')->__('Manage Single Step Checkout');
        }

        protected function getSaveButtonHtml()
        {
            return $this->getChildHtml('save_button');
        }
        
        protected function getSaveFormAction(){
            return $this->getUrl('*/*/save');
        }
    }
?>