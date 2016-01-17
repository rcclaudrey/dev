<?php

class Wyomind_Googletrustedstores_Adminhtml_GoogletrustedstoresController extends Mage_Adminhtml_Controller_Action {

    public function orderpageAction() {
        Mage::getSingleton('checkout/session')->setData('gts_order_page_test',true);
        $this->loadLayout();
        $this->getLayout()
                ->getBlock('root')
                ->setData('area','frontend')
                ->setTemplate('googletrustedstores/details.phtml');
        $this->renderLayout();
        return $this;
    }
    
    public function badgeAction() {
        Mage::getSingleton('checkout/session')->setData('gts_badge_test',true);
        $this->loadLayout();
        $this->getLayout()
                ->getBlock('root')
                ->setData('area','frontend')
                ->setTemplate('googletrustedstores/badge.phtml');
        $this->renderLayout();
        return $this;
    }
    
    
   
    
}
