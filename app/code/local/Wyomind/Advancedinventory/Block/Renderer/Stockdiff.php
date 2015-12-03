<?php

class Wyomind_Advancedinventory_Block_Renderer_Stockdiff extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {

        if (in_array($row->getTypeId(), array('simple', 'virtual', 'downloadable'))) {

            $localQty = Mage::getModel('advancedinventory/advancedinventory')->getLocalGlobalStockByProductId($row->getId());
            $diff = $row->getQty() - $localQty->getTotalQuantityInStock();


            if (!$localQty->getManageLocalStock())
                $html = "<span id='stock_difference_" . $row->getId() . "' style=''>-</span>";
            else {
                if ($diff == 0): $html = "<span  style='color:green'> == </span>";
                elseif ($diff > 0):
                    $html = "<span  style='color:red'><b style='font-size:16px;'>> </b>  (+ " . $diff . ")</span>";
                else :
                    $html = "<span  style='color:orange; '><b style='font-size:16px;'>< </b>  (" . $diff . ")</span>";
                endif;
            }
            return $html;
        }
        else
            return "-";
    }

}
