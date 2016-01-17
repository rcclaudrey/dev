<?php

require_once("app/code/local/Wyomind/Advancedinventory/Block/Adminhtml/Widget/Grid.php");

class Wyomind_Advancedinventory_Block_Adminhtml_Sales_Order_Grid extends Mage_Adminhtml_Block_Sales_Order_Grid {

    protected function _prepareColumns() {

        if (Mage::getModel('pointofsale/pointofsale')->getPlaces()->count() > 0) {

            $places = Mage::getModel('pointofsale/pointofsale')->getPlaces();

            $inventories[0] = Mage::helper('advancedinventory')->__('Not Assigned');

            foreach ($places as $p) {

                $inventories[$p->getPlaceId()] = $p->getName() . ' (' . $p->getStoreCode() . ')';
            }



            $this->addColumn('assignation', array(
                'header' => Mage::helper('sales')->__('Assigned to '),
                'index' => 'assignation',
                'type' => 'options',
                'width' => '150px',
                'options' => $inventories,
                'renderer' => "Wyomind_Advancedinventory_Block_Renderer_Assignation",
            ));

            //if(version_compare(Mage::getVersion(), '1.3.0', '>')) {

            $this->addColumnsOrder('assignation', 'status');

            $this->sortColumnsByOrder();

            //}
        }

        parent::_prepareColumns();
    }

}