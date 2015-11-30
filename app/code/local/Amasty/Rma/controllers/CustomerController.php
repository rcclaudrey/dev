<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Rma
 */ 
require_once Mage::getModuleDir('controllers', 'Amasty_Rma').DS.'GuestController.php';


class Amasty_Rma_CustomerController extends Amasty_Rma_GuestController
{
    function historyAction(){
        $this->loadLayout();
        $this->_initLayoutMessages('catalog/session');

        $this->getLayout()->getBlock('head')->setTitle($this->__('My RMA'));

        if ($block = $this->getLayout()->getBlock('customer.account.link.back')) {
            $block->setRefererUrl($this->_getRefererUrl());
        }
        
        $this->renderLayout();
    }
}
?>