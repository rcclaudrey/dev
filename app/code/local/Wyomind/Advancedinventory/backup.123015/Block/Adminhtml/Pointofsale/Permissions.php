<?php


class Wyomind_Advancedinventory_Block_Adminhtml_Pointofsale_Permissions extends Mage_Adminhtml_Block_Widget_Container {

    public function __construct() {

        $this->_controller = 'adminhtml_permissions';

        $this->_blockGroup = 'advancedinventory';

        $this->_headerText = Mage::helper('advancedinventory')->__('Manage product stocks');

        $this->_addButton('save', array(
            'label' => Mage::helper('advancedinventory')->__('Save all changes'),
            'class' => 'save',
            'onclick' => "POSPermissions.save();",
        ));

        $this->_addButton('reset', array(
            'label' => Mage::helper('advancedinventory')->__('Reset'),
            'class' => 'delete',
            'onclick' => "POSPermissions.reinit();"
        ));
        
        parent::__construct();
        $this->setTemplate('pointofsale/permissions.phtml');
        $this->removeButton('add');
    }


}