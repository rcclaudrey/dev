<?php

class Wyomind_Advancedinventory_Block_Adminhtml_Sales_Order_Renderer_Assignation extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {
        if ($row->getAssignationWarehouse() == '-1')
            return "<div style='color:grey;'>" . Mage::helper('advancedinventory')->__("Order placed before multistock initialization") . "</div>";


        $link_open = '<a class="assignation_cell" href="javascript:InventoryManager.changeAssignation(' . $row->getId() . ',' . $this->getRequest()->getParam('store', 0) . ',\'' . $this->getUrl('advancedinventory/adminhtml_stocks/details') . '\')" title="' . Mage::helper('advancedinventory')->__('Change assignation') . '">';
        $link_close = "</a>";
        try {
            $data = Mage::helper('core')->jsonDecode($row->getAssignationStock());
        } catch (Exception $e) {
            return "<span class='ai-error'>" . Mage::helper('advancedinventory')->__("Assignation error") . "</span>";
        }
        $warehouses = array();
        $warnings = 0;
        $items = Mage::helper('advancedinventory/data')->getOrderedItems($row);
        foreach ($items as $item) {
            $qty = 0;
            foreach ($data[$item["id"]] as $wh => $q) {

                if ($q > 0) {
                    if (!isset($warehouses[$wh]))
                        $warehouses[$wh] = 0;
                    $warehouses[$wh] += $q;
                }
                $qty+=$q;
            };
            if ($item['qty'] > $qty && Mage::getModel('advancedinventory/stock')->getMultiStockEnabledByProductId($item['id'])) {
                $warnings++;
            }
        }
        $assignations = array();
        foreach ($warehouses as $wh => $qty) {
            $p = Mage::getModel('pointofsale/pointofsale')->load($wh);
            $assignations[] = "<div style='font-size:12px; color:green'>" . $p->getName() . '</div>';
        }
        $return = $link_open;



        $return .= implode('', $assignations);
        $color = (!Mage::helper('advancedinventory')->getAllowedOrder($row)) ? "grey" : "red";
        $bold = (!Mage::helper('advancedinventory')->getAllowedOrder($row)) ? "normal" : "bold";

        if ($warnings == 1)
            $return.="<span style='color:$color; font-weight:$bold'>" . $warnings . " " . Mage::helper('advancedinventory')->__("item is not assigned") . "</span><br>";
        else if ($warnings > 1)
            $return.="<span style='color:$color; font-weight:$bold'>" . $warnings . " " . Mage::helper('advancedinventory')->__("items are not assigned") . "</span><br>";
        else if (!$warnings && !count($assignations))
            $return.= "<div style='color:grey;'>" . Mage::helper('advancedinventory')->__("No assignation required") . "</div>";

        $return.= $link_close;

        return "<div id='order_summary_" . $row->getId() . "'>" . $return . "</div>";
    }

}
