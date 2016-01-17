<?php

class Wyomind_Googletrustedstores_ValidatorController extends Mage_Core_Controller_Front_Action {

    public function orderAction() {
        Mage::register('order',Mage::getModel('sales/order')->loadByIncrementId($this->getRequest()->getParam('id'))->getId() );
  
        $this->loadLayout();
        $this->renderLayout();
        return $this;
    }

    public function badgeAction() {
        Mage::register('product', Mage::getModel("catalog/product")->loadByAttribute("sku",$this->getRequest()->getParam('id')));
        Mage::register('current_product', Mage::getModel("catalog/product")->loadByAttribute("sku",$this->getRequest()->getParam('id')));
        $this->loadLayout();
        $this->renderLayout();
        return $this;
    }

}
