<?php

class Wyomind_Advancedinventory_Block_Renderer_Difference extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {

        if (in_array($row->getTypeId(), array('simple', 'virtual', 'downloadable'))) {
            if (!$row->getManageLocalStock())
                $html = "<span id='stock_difference_" . $row->getId() . "' style=''>-</span>";
            else {
                if ($row->getDifference() == 0): $html = "<span id='stock_difference_" . $row->getId() . "' style='color:green'> == </span>";
                elseif ($row->getDifference() > 0):
                    $html = "<span id='stock_difference_" . $row->getId() . "' style='color:red'> <b style='font-size:16px;'>> </b>  (+" . $row->getDifference() . ") </span>";
                else :
                    $html = "<span id='stock_difference_" . $row->getId() . "' style='color:orange; '><b style='font-size:16px;'>< </b>(" . ($row->getDifference()) . ")</span>";
                endif;
            }
            return "<input type='hidden' value='" . $row->getManageLocalStock() . "' id='manage_local_stock_" . $row->getId() . "' name='inventory[" . $row->getId() . "][manage_local_stock]'>" . $html;
        }
        else
            return "-";
    }

}
