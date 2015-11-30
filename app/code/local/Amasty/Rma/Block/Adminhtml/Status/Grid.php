<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Rma
 */ 
class Amasty_Rma_Block_Adminhtml_Status_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('statusGrid');
      $this->setDefaultSort('order_number');
      $this->setDefaultDir('asc');
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('amrma/status')
              ->getCollection()
              ->addLabel();
      
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
    $hlp =  Mage::helper('amrma'); 
    
    $this->addColumn('status_id', array(
      'header'    => $hlp->__('ID'),
      'align'     => 'right',
      'width'     => '50px',
      'index'     => 'status_id',
    ));
    
    $this->addColumn('is_active', array(
        'header'    => $hlp->__('Status'),
        'align'     => 'left',
        'width'     => '80px',
        'index'     => 'is_active',
        'type'      => 'options',
        'width'     => '80px',
        'options'   => $hlp->getStatuses()
    ));
    
    $this->addColumn('label', array(
      'header'    => $hlp->__('Name'),
      'align'     => 'left',
      
      'index'     => 'label',
    ));
    
    $this->addColumn('order_number', array(
      'header'    => $hlp->__('Sort'),
      'align'     => 'left',
      'width'     => '50px',
      'index'     => 'order_number',
    ));
    
    return parent::_prepareColumns();
  }

  public function getRowUrl($row)
  {
      return $this->getUrl('*/*/edit', array('id' => $row->getId()));
  }
  
    protected function _prepareMassaction()
    {
        $hlr = Mage::helper('amrma');
        
        $this->setMassactionIdField('status_id');
        $this->getMassactionBlock()->setFormFieldName('statuses');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => $hlr->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => $hlr->__('Are you sure?')
        ));
        
        $this->getMassactionBlock()->addItem('activate', array(
             'label'    => $hlr->__('Activate'),
             'url'      => $this->getUrl('*/*/massActivate'),
        ));
        
        $this->getMassactionBlock()->addItem('inactivate', array(
             'label'    => $hlr->__('Inactivate'),
             'url'      => $this->getUrl('*/*/massInactivate')
        ));

        return $this; 
    }
  
}