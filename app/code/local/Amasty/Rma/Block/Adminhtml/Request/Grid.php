<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Rma
 */ 
class Amasty_Rma_Block_Adminhtml_Request_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('requestGrid');
        $this->setDefaultSort('request_id');
        
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('amrma/request')
                ->getCollection()
                ->addStatusLabel();
        
        $this->setCollection($collection);
        
        return parent::_prepareCollection();
    }
    
    protected function _filterStoreCondition($collection, $column){
        if (!$value = $column->getFilter()->getValue()) {
            return;
        }
        $this->getCollection()->addStoreFilter($value);
    }

    protected function _prepareColumns()
    {
        $hlp =  Mage::helper('amrma'); 

        $statusOptions = $hlp->getRequestStatuses();
        $booleanOptions = $hlp->getBooleanOptions();
        
        $this->addColumn('request_id', array(
          'header'    => $hlp->__('ID'),
          'align'     => 'right',
          'width'     => '50px',
          'index'     => 'request_id',
        ));

        $this->addColumn('store_id', array(
            'header'        => $hlp->__('Store View'),
            'index'         => 'store_id',
            'filter_index'  => 'main_table',
            'type'          => 'store',
            'store_all'     => true,
            'store_view'    => true,
            'sortable'      => true,
            'width' => 150,
            'filter_condition_callback' => array($this,
                '_filterStoreCondition'),
        ));
        
        $this->addColumn('increment_id', array(
          'header'    => $hlp->__('Order ID'),
          'index'     => 'increment_id',
          'width'     => 80,
        ));
        
        $this->addColumn('created', array(
          'header' => $hlp->__('Created'),
          'index' => 'created',
          'type' => 'datetime',
          'width' => 150,
        ));
        
        $this->addColumn('updated', array(
          'header' => $hlp->__('Updated'),
          'index' => 'updated',
          'type' => 'datetime',
          'width' => 150,
        ));
        
        $this->addColumn('status_id', array(
          'header' => $hlp->__('Status'),
          'type'      => 'options',
          'options'   => $statusOptions,
          'filter_index' => 'main_table.status_id',
          'index' => 'label',
          'width' => 200,
        ));
        
        $this->addColumn('is_shipped', array(
          'header'    => $hlp->__('Is Shipped'),
          'index'     => 'is_shipped',
          'type'      => 'options',
          'options' => $booleanOptions,
          'width' => 50,
        ));
        
        $this->addColumn('customer_lastname', array(
            'header'    => $hlp->__('Customer Name'),
            'index' => array('customer_firstname', 'customer_lastname'),
            'filter_index' => "CONCAT(customer_firstname, ' -- ', customer_lastname)",
            'separator' => ' ',
            'renderer' => 'adminhtml/widget_grid_column_renderer_concat',
        ));
        
        $this->addColumn('email', array(
          'header'    => $hlp->__('Customer Email'),
          'index'     => 'email',
        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
          return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }
  
}