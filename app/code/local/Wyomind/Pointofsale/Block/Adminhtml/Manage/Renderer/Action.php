<?php

class Wyomind_Pointofsale_Block_Adminhtml_Manage_Renderer_Action extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Action {

    public function render(Varien_Object $row) {
        $this->getColumn()->setActions(
                array(
                    array(
                        'url' => $this->getUrl('*/adminhtml_manage/edit', array('place_id' => $row->getPlace_id())),
                        'caption' => Mage::helper('pointofsale')->__('Edit'),
                    ),
                    array(
                        'url' => $this->getUrl('*/adminhtml_manage/delete', array('place_id' => $row->getPlace_id())),
                        'confirm' => Mage::helper('pointofsale')->__('Are you sure you want to delete this pos / warehouse ?'),
                        'caption' => Mage::helper('pointofsale')->__('Delete'),
                    ),
                )
        );
        return parent::render($row);
    }

}
