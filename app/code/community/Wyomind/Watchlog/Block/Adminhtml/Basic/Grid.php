<?php

class Wyomind_Watchlog_Block_Adminhtml_Basic_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId("watchlogGrid");
        $this->setDefaultSort("date");
        $this->setDefaultDir("DESC");
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection() {
        $collection = Mage::getModel("watchlog/watchlog")->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {

        $this->addColumn("ip", array(
            "header" => Mage::helper("watchlog")->__("IP"),
            "width" => "100px",
            "align" => "center",
            'renderer' => 'Wyomind_Watchlog_Block_Adminhtml_Renderer_Ip',
            "index" => "ip",
        ));
        $this->addColumn('date', array(
            'header' => Mage::helper('watchlog')->__('Date'),
            'index' => 'date',
            'width' => '200px',
            'type' => 'datetime',
        ));

        $this->addColumn("login", array(
            "header" => Mage::helper("watchlog")->__("Login"),
            "index" => "login",
            'width' => '200px',
        ));
        $this->addColumn('message', array(
            'header' => Mage::helper('watchlog')->__('Message'),
            'index' => 'message',
            'width' => '200px',
        ));
        $this->addColumn('url', array(
            'header' => Mage::helper('watchlog')->__('Url'),
            'index' => 'url',
        ));
        $this->addColumn('type', array(
            'header' => Mage::helper('watchlog')->__('Status'),
            'index' => 'type',
            'renderer' => 'Wyomind_Watchlog_Block_Adminhtml_Renderer_Status',
            'width' => '100px',
            'filter' => false,
        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row) {
        return '#';
    }

}
