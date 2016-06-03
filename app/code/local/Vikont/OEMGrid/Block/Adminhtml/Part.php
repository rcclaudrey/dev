<?php

class Vikont_OEMGrid_Block_Adminhtml_Part extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_part';
        $this->_blockGroup = 'oemgrid';
        $this->_headerText = $this->__('OEM Parts Manager');
//        $this->_addButtonLabel = $this->__('Add Part');

		$this->_addButton('import', array(
            'label'     => $this->__('Import data'),
            'onclick'   => 'setLocation(\'' . $this->getUrl('*/adminhtml_import') .'\')',
//            'class'     => 'add',
        ));

		parent::__construct();

		$this->_removeButton('add');
    }
}