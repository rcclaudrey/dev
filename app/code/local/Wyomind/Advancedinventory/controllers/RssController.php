<?php

class Wyomind_Advancedinventory_RssController extends Mage_Core_Controller_Front_Action {

    public function StockAction() {
        $this->getResponse()->setHeader('Content-type', 'text/xml; charset=UTF-8');
        $this->loadLayout(false);
        $this->renderLayout();
    }

    public function preDispatch() {
        if ($this->getRequest()->getActionName() == 'stock') {
            $this->_currentArea = 'adminhtml';
            Mage::helper('rss')->authAdmin('catalog/products');
        }
        
        return parent::preDispatch();
    }

}
