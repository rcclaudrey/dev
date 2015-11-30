<?php
class Wyomind_Pointofsale_Block_Adminhtml_Manage extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_manage';
    $this->_blockGroup = 'pointofsale';
    $this->_headerText = Mage::helper('pointofsale')->__('Manage POS / Warehouses');
    $this->_addButtonLabel = Mage::helper('pointofsale')->__('Add one POS / Warehouse');
    
    
    $this->_addButton('export', array(
        'label'   => Mage::helper('pointofsale')->__('Export a csv file'),
        'class'   => 'save',
        'onclick' => "setLocation('".$this->getUrl('*/*/exportCsv')."')"
    ));
    
    $this->_addButton('import', array(
        'label'   => Mage::helper('pointofsale')->__('Import a csv file'),
        'class'   => 'save',
        'onclick' => "setLocation('".$this->getUrl('*/*/importCsv')."')"
    ));
  
    
    parent::__construct();
  }
}