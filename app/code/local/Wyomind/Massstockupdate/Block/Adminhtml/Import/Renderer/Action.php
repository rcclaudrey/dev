<?php

class Wyomind_Massstockupdate_Block_Adminhtml_Import_Renderer_Action extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Action {

    public function render(Varien_Object $row) {
        $this->getColumn()->setActions(
                array(
                    array(
                        'url' => $this->getUrl('*/adminhtml_import/edit', array('profile_id' => $row->getProfile_id())),
                        'caption' => Mage::helper('massstockupdate')->__('Edit'),
                    ),
                    array(
                        'url' => $this->getUrl('*/adminhtml_import/delete', array('profile_id' => $row->getProfile_id())),
                        'confirm' => Mage::helper('massstockupdate')->__('Are you sure you want to delete this profile ?'),
                        'caption' => Mage::helper('massstockupdate')->__('Delete'),
                    ),
                      array(
                        'url' => $this->getUrl('*/adminhtml_import/run', array('profile_id' => $row->getProfile_id())),
                        'confirm' => Mage::helper('massstockupdate')->__('All your stock will be updated. Continue ?'),
                        'caption' => Mage::helper('massstockupdate')->__('Run profile'),
                    ),
                    
                )
        );
        return parent::render($row);
    }

}
