<?php

class Wyomind_Pointofsale_Block_Adminhtml_Manage_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('pointofsaleGrid');
        $this->setDefaultSort('position');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(false);
    }

    protected function _prepareCollection() {

        $collection = Mage::getModel('pointofsale/pointofsale')->getCollection();
        $this->setCollection($collection);
        parent::_prepareCollection();
      
        return $this;
    }

    protected function _prepareColumns() {


        $this->addColumn('store', array(
            'header' => Mage::helper('pointofsale')->__('Store'),
            'renderer' => 'Wyomind_Pointofsale_Block_Adminhtml_Manage_Renderer_Store',
            'width' => '400',
            'sortable' => false,
            'filter' => false
        ));

        $this->addColumn('store_id', array(
            'header' => Mage::helper('pointofsale')->__('Store view selection'),
            'renderer' => 'Wyomind_Pointofsale_Block_Adminhtml_Manage_Renderer_Storeview',
            'sortable' => false,
            'filter' => false
        ));
        $this->addColumn('customer_group', array(
            'header' => Mage::helper('pointofsale')->__('Customer Group selection'),
            'renderer' => 'Wyomind_Pointofsale_Block_Adminhtml_Manage_Renderer_Customergroup',
            'sortable' => false,
            'filter' => false
        ));
        $this->addColumn('position', array(
            'header' => Mage::helper('pointofsale')->__('Order'),
            'index' => 'position',
            'width' => '50',
            'sortable' => true,
            'filter' => false
        ));
        $this->addColumn('status', array(
            'header' => Mage::helper('pointofsale')->__('Status'),
            'align' => 'left',
            'width' => '80px',
            'index' => 'status',
            'type' => 'options',
            'options' => array(
                0 => Mage::helper('pointofsale')->__('Warehouse (hidden)'),
                1 => Mage::helper('pointofsale')->__('Point of Sales (visible)'),
            ),
        ));
        $this->addColumn('action', array(
            'header' => Mage::helper('pointofsale')->__('Action'),
            'align' => 'left',
            'index' => 'action',
            'filter' => false,
            'sortable' => false,
            'renderer' => 'Wyomind_Pointofsale_Block_Adminhtml_Manage_Renderer_Action',
        ));


        return parent::_prepareColumns();
    }

    public function getRowUrl($row) {
        return $this->getUrl('*/*/edit', array('place_id' => $row->getId()));
    }

}
