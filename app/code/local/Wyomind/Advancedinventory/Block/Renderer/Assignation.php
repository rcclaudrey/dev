<?php

class Wyomind_Advancedinventory_Block_Renderer_Assignation extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {

        $places = Mage::getModel('pointofsale/pointofsale')->getPlaces();
        $inventory = Mage::helper('advancedinventory')->__('Not Assigned');
        $options[] = "<option value='0'>" . $inventory . "</option>";
        foreach ($places as $p) {
            $inventoryName = $p->getName() . ' (' . $p->getStoreCode() . ')';
            if ($row->getAssignation() == $p->getPlaceId()) {
                $inventory = $inventoryName;
                $selected = 'selected';
            }
            else
                $selected = null;
            $options[] = "<option " . $selected . " value='" . $p->getPlaceId() . "'>" . $inventoryName . "</option>";
        }
        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/change_assignation') && (!in_array($row->getStatus(), array('canceled', 'closed', 'complete', 'fraud', 'holded')))):



            $html = "<select  onchange='InventoryManager.changeAssignation(" . $row->getId() . ",this.value,\"" . $this->getUrl('advancedinventory/adminhtml_stocks/reassign') . "\")'>
            <optgroup label='" . Mage::helper('advancedinventory')->__('Change to ...') . "'>";
            foreach ($options as $option) {
                $html.=$option;
            }

            $html .= "</optgroup>
            </select>";
        else :
            return $inventory;
        endif;

        return $html;
    }

}
