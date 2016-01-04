<?php

class Wyomind_Advancedinventory_Block_Adminhtml_Stocks_Renderer_Action extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Action {

    public function render(Varien_Object $row) {

        $permissions = Mage::helper('advancedinventory/permissions')->getUserPermissions();
        $all = $permissions->isAdmin();


        $manage_stock = Mage::getStoreConfig("cataloginventory/item_options/manage_stock");
        if ($row->getManageStock() || ($row->getUse_config_manage_stock() && $manage_stock )) {
            if (in_array($row->getTypeId(), array('simple', 'virtual', 'downloadable'))) {
                $actions[] = array(
                    'url' => "javascript:InventoryManager.saveStocks('" . $this->getUrl('*/*/save', array('id' => $row->getId(), "is_admin" => (int) $all, "store_id" => Mage::app()->getRequest()->getParam('store', 0))) . "','" . $row->getId() . "')",
                    'caption' => Mage::helper('advancedinventory')->__('Save change'),
                    'id' => 'save'
                );
                if ($all) {
                    if (Mage::getModel('pointofsale/pointofsale')->getPlaces()->count()) {

                        if (Mage::app()->getRequest()->getParam('store') == Mage_Core_Model_App::ADMIN_STORE_ID) {
                            $actions[] = array(
                                'caption' => Mage::helper('advancedinventory')->__((!$row->getMultistock_enabled()) ? Mage::helper('advancedinventory')->__("Enable multi-stock") : Mage::helper('advancedinventory')->__("Disable multi-stock")),
                                'url' => "javascript:InventoryManager.enableMultiStock('grid'," . $row->getId() . ")",
                                'id' => 'enable'
                            );
                        }
                    }
                }
            }
        }
        if ($all) {
            $actions[] = array(
                'url' => $this->getUrl('adminhtml/catalog_product/edit', array('id' => $row->getId(), "tab" => "product_info_tabs_inventory")),
                'caption' => Mage::helper('advancedinventory')->__('Edit product'),
                'popup' => true,
                'id' => 'edit'
            );
        }
        $this->getColumn()->setActions(
                $actions
        );
        return parent::render($row);
    }

}
