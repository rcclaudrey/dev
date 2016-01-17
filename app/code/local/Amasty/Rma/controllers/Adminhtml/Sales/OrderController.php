<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Rma
 */ 
    $defController = Mage::getBaseDir()
    . DS . 'app' . DS . 'code' . DS . 'core'
    . DS . 'Mage' . DS . 'Adminhtml' . DS . 'controllers'
    . DS . 'Sales' . DS . 'OrderController.php';
    
    
    require_once $defController;
    
    class Amasty_Rma_Adminhtml_Sales_OrderController extends Mage_Adminhtml_Sales_OrderController
    {
        public function rmaAction()
        {
            $this->_initOrder();
            $this->getResponse()->setBody(
                $this->getLayout()->createBlock('amrma/adminhtml_sales_order_view_tab_rma')->toHtml()
            );
        }
    }
?>