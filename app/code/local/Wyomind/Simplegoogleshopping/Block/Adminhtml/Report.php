<?php

class Wyomind_Simplegoogleshopping_Block_Adminhtml_Report extends Mage_Adminhtml_Block_Template {

    public function _ToHtml() {


        $id = $this->getRequest()->getParam('simplegoogleshopping_id');
        $googleshopping = Mage::getModel('simplegoogleshopping/simplegoogleshopping');
        $googleshopping->_limit = $this->getRequest()->getParam('limit');
        $googleshopping->_display = $this->getRequest()->getParam('display');
        $googleshopping->setId($id);
        if ($googleshopping->load($id) && !$googleshopping->_display) {
            return ( Mage::helper('simplegoogleshopping')->reportToHtml(unserialize($googleshopping->getSimplegoogleshoppingReport())));
        } else {
            $googleshopping->generateXml();
          
            return Mage::helper('simplegoogleshopping')->reportToHtml($googleshopping->_errorReport);
        }
    }

}
