<?php

class Wyomind_Massstockupdate_Block_Adminhtml_Import_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {

        parent::__construct();
        $this->setId('massstockupdateImport');
        $this->setDefaultSort('massstockupdate_importprofiles_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection() {

        $collection = Mage::getModel('massstockupdate/import')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        $this->addColumn('profile_id', array(
            'header' => Mage::helper('massstockupdate')->__('ID'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'profile_id',
            'filter' => false,
        ));

        $this->addColumn('profile_name', array(
            'header' => Mage::helper('massstockupdate')->__('Profile name'),
            'align' => 'left',
            'index' => 'profile_name',
           
          
        ));

        $this->addColumn('imported_at', array(
            'header' => Mage::helper('massstockupdate')->__('Last execution'),
            'align' => 'left',
            'index' => 'imported_at',
            'width' => '80px',
            'type' => "datetime"
        ));
        
       $this->addColumn('action', array(
            'header' => Mage::helper('massstockupdate')->__('Action'),
            'align' => 'left',
            'index' => 'action',
            'filter' => false,
            'sortable' => false,
           'width' => '120px',
            'renderer' => 'Wyomind_Massstockupdate_Block_Adminhtml_Import_Renderer_Action',
        ));





        return parent::_prepareColumns();
    }

    public function getRowUrl($row) {
        return $this->getUrl('*/*/edit', array('profile_id' => $row->getProfileId()));
    }

}