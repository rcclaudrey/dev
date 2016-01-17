<?php

class Wyomind_Simplegoogleshopping_Block_Adminhtml_Sample extends Mage_Adminhtml_Block_Template {

    public function _ToHtml() {
        $id = $this->getRequest()->getParam('simplegoogleshopping_id');


        $googleshopping = Mage::getModel('simplegoogleshopping/simplegoogleshopping');
        $googleshopping->setId($id);
        $googleshopping->_limit = Mage::getStoreConfig("simplegoogleshopping/system/preview");
        $googleshopping->_display = true;

        // if googleshopping record exists
        $googleshopping->load($id);
        $content = $googleshopping->generateXml();
        if ($googleshopping->_demo) {
            Mage::getConfig()->saveConfig('simplegoogleshopping/license/activation_code', '', 'default', '0');
            Mage::getConfig()->cleanCache();
            return "<div id='dfm-report' style='color:red'><br><br><b>" . Mage::helper('simplegoogleshopping')->__('Invalid license.') . "</b></div>";
        } else {
            return "<textarea id='CodeMirror' class='CodeMirror'>" . $content . "</textarea>";
        }
    }

}
