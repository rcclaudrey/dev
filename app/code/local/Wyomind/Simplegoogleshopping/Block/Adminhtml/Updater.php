<?php

class Wyomind_Simplegoogleshopping_Block_Adminhtml_Updater extends Mage_Adminhtml_Block_Template {

    public function _ToHtml() {

        $json = array();
        $data = Mage::helper('core')->jsonDecode($this->getRequest()->getPost('data'));
        foreach ($data as $f) {
            $row = new Varien_Object;
            $row->setId($f[0]);
            $row->setCronExpr($f[1]);
            $status = new Wyomind_Simplegoogleshopping_Block_Adminhtml_Simplegoogleshopping_Renderer_Status();
            $json[] = array("id" => $f[0], "content" => ($status->render($row)));
        }
        return (Mage::helper('core')->jsonEncode($json));
    }

}
