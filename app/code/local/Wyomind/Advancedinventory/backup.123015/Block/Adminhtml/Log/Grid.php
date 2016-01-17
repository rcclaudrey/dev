<?php

class Wyomind_Advancedinventory_Block_Adminhtml_Log_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('logGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection() {
        $collection = Mage::getModel('advancedinventory/log')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        $this->addColumn('id', array(
            'header' => Mage::helper('catalog')->__('ID'),
            'type' => 'number',
            'index' => 'id',
        ));
        $this->addColumn('datetime', array(
            'header' => Mage::helper('catalog')->__('Date/Time'),
            'width' => '150px',
            'type' => 'datetime',
            'index' => 'datetime',
        ));

        $options = Mage::getModel("advancedinventory/log")->getUsers();

        $this->addColumn('user', array(
            'header' => Mage::helper('catalog')->__('User'),
            'width' => '100px',
            'type' => 'options',
            'options' => $options,
            'index' => 'user',
        ));
        $options = Mage::getModel("advancedinventory/log")->getContexts();
        $this->addColumn('context', array(
            'header' => Mage::helper('catalog')->__('Context'),
            'width' => '100px',
            'type' => 'options',
            'options' => $options,
            'index' => 'context',
        ));
        $options = Mage::getModel("advancedinventory/log")->getActions();
        $this->addColumn('action', array(
            'header' => Mage::helper('catalog')->__('Action'),
            'width' => '150px',
            'type' => 'options',
            'options' => $options,
            'index' => 'action',
        ));
        $this->addColumn('reference', array(
            'header' => Mage::helper('catalog')->__('Reference'),
            'width' => '150px',
            'type' => 'text',
            'index' => 'reference',
            'renderer' => "Wyomind_Advancedinventory_Block_Adminhtml_Log_Renderer_Reference",
        ));
        $this->addColumn('details', array(
            'header' => Mage::helper('catalog')->__('Details'),
            'width' => '',
            'type' => 'text',
            'index' => 'details',
            "filter" => false,
            "sortable" => false
        ));
        return parent::_prepareColumns();
    }

    public function getRowUrl($row) {
        return false;
    }

    protected function _prepareMassaction() {

        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('ids');
        $this->getMassactionBlock()->addItem('delete', array(
            'label' => Mage::helper('advancedinventory')->__('Purge'),
            'value' => true,
            'url' => $this->getUrl('*/*/MassDelete')
        ));

        Mage::dispatchEvent('adminhtml_catalog_product_grid_prepare_massaction', array('block' => $this));
        return $this;
    }

}
