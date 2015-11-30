<?php
    /**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Rma
 */ 
    class Amasty_Rma_Block_Adminhtml_Request_Edit_Tab_Items extends Mage_Adminhtml_Block_Widget_Form
    {
        public function __construct()
        {
            parent::__construct();
            $this->setTemplate('amasty/amrma/request/items.phtml');
        }
        
        public function getRmaItems(){
            
            $collection = Mage::getModel('amrma/item')
                    ->getCollection()
                    ->addFilter('request_id', $this->getModel()->getId());
            
            return $collection;
        }
    }
?>