<?php

class Wyomind_Advancedinventory_Block_Adminhtml_Pointofsale_Edit_Tabs extends Wyomind_Pointofsale_Block_Adminhtml_Manage_Edit_Tabs {

    protected function _beforeToHtml() {



        if ( Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/change_assignation')) {

            parent::_beforeToHtml();

            $this->addTab('deliveryrules', array(
                'label' => Mage::helper('pointofsale')->__('Inventory setting'),
                'title' => Mage::helper('pointofsale')->__('Inventory setting'),
                'content' => $this->getLayout()->createBlock('advancedinventory/adminhtml_pointofsale_edit_tab_advancedinventory')->toHtml(),
            ));
        }



        return parent::_beforeToHtml();
    }

}