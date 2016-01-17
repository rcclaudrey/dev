<?php

class Wyomind_Advancedinventory_Block_Renderer_Action extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Action {

    public function render(Varien_Object $row) {
        if (in_array($row->getTypeId(), array('simple', 'virtual', 'downloadable'))) {
            $actions[] =
                    array(
                        'url' => "javascript:InventoryManager.save('" . $this->getUrl('*/*/save', array('id' => $row->getId())) . "','" . $row->getId() . "')",
                        'caption' => Mage::helper('advancedinventory')->__('Save'),
                        'id' => 'save'
            );

            if (Mage::getModel('pointofsale/pointofsale')->getPlaces()->count()) {
                if (Mage::getStoreConfig("advancedinventory/setting/lock_online_stock")) {
                    $actions[] = array(
                        'caption' => Mage::helper('advancedinventory')->__("Sync. global stock"),
                        'url' => "javascript:InventoryManager.recalculate( " . $row->getId() . ")",
                        'id' => 'synchronize'
                    );
                }
                $actions[] = array(
                    'caption' => Mage::helper('advancedinventory')->__((!$row->getManageLocalStock()) ? Mage::helper('advancedinventory')->__("Enable local stocks") : Mage::helper('advancedinventory')->__("Disable local stocks")),
                    'url' => "javascript:InventoryManager.displayLocalStocks( " . $row->getId() . "," . ((!$row->getManageLocalStock()) ? "true" : "false") . ")",
                    'id' => 'enable'
                );
            }
        }
        $actions[] = array(
            'url' => $this->getUrl('adminhtml/catalog_product/edit', array('id' => $row->getId(), "tab" => "product_info_tabs_inventory")),
            'caption' => Mage::helper('advancedinventory')->__('Edit'),
            'popup' => true,
            'id' => 'edit'
        );
        $this->getColumn()->setActions(
                $actions
        );
        return parent::render($row);
    }

}

