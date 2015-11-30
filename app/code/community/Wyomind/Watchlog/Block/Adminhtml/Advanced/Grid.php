<?php

class Wyomind_Watchlog_Block_Adminhtml_Advanced_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('watchlogGrid');
        $this->setDefaultSort('attempts');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection() {

        $collection = Mage::getModel('watchlog/watchlog')->getSummary();
        
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {


      

        $this->addColumn('ip', array(
            'header' => Mage::helper('watchlog')->__('IP'),
            'width' => '100px',
            'align'=>'center',
            "type" => "text",
            'renderer' => 'Wyomind_Watchlog_Block_Adminhtml_Renderer_Ip',
            'index' => "ip"
        ));
        
        
        $this->addColumn('date', array(
            'header' => Mage::helper('watchlog')->__('Last attempt'),
            'width' => 'auto',
            "type" => "datetime",
            'index' => "date"
        ));
        
        $this->addColumn('attempts', array(
            'header' => Mage::helper('watchlog')->__('Attempts'),
            'width' => '100px',
            "type" => "number",
            'index' => "attempts",
            'filter'=>false,
        ));
        
        $this->addColumn('failed', array(
            'header' => Mage::helper('watchlog')->__('Failed'),
            'width' => '100px',
            "type" => "number",
            'index' => "failed",
            'filter'=>false,
        ));
        
        $this->addColumn('succeeded', array(
            'header' => Mage::helper('watchlog')->__('Succeeded'),
            'width' => '100px',
            "type" => "number",
            'index' => "succeeded",
            'filter'=>false,
        ));
        
        return parent::_prepareColumns();
    }
    public function getRowUrl($row) {
        return '#';
    }
    

}