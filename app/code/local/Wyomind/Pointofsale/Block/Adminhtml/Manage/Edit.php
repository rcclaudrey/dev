<?php

class Wyomind_Pointofsale_Block_Adminhtml_Manage_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {

    public function __construct() {
        parent::__construct();

        $this->_objectId = 'edit';
        $this->_blockGroup = 'pointofsale';
        $this->_controller = 'adminhtml_manage';

        $this->_updateButton('save', 'label', Mage::helper('pointofsale')->__('Save and go back'));
        $this->_updateButton('delete', 'label', Mage::helper('pointofsale')->__('Delete this POS / Warehouse'));

        $this->_addButton('saveandcontinue', array(
            'label' => Mage::helper('adminhtml')->__('Save and Continue'),
            'onclick' => 'saveAndContinueEdit()',
            'class' => 'save',
                ), -100);

        if ($this->getRequest()->getParam('place_id')) {
            $this->_addButton('delete', array(
                'label' => Mage::helper('adminhtml')->__('Delete this Pos/Warehouse'),
                'onclick' => 'deleteThisPlace()',
                'class' => 'delete',
                    ), -100);
        }

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('pointofsale_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'pointofsale_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'pointofsale_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
            
            function deleteThisPlace(){
               setLocation('" . $this->getUrl('*/*/delete', array("place_id" => $this->getRequest()->getParam('place_id'))) . "')
            }
        ";
    }

    public function getHeaderText() {

        if ($this->getRequest()->getParam('place_id')) {

            $place = Mage::getModel('pointofsale/pointofsale')->load($this->getRequest()->getParam('place_id'));
    
            return Mage::helper('pointofsale')->__("Edit '%s'", $place->getName() . ' [' . $place->getStoreCode() . ']');
           
        } else {
            return Mage::helper('pointofsale')->__('Add one POS / Warehouse');
        }
    }

}