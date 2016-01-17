<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Fpc
 */


class Amasty_Fpc_Block_Adminhtml_Log_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('amfaqLogGrid');
        $this->setDefaultSort('rate');
        $this->setDefaultDir('DESC');
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('amfpc/url_collection');

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        parent::_prepareColumns();

        $this->addColumn(
            'url',
            array(
                'index'  => 'url',
                'header' => $this->__('URL'),
            )
        );

        $this->addColumn(
            'rate',
            array(
                'header' => $this->__('Rate'),
                'index'  => 'rate',
            )
        );

        return $this;
    }

    public function getRowUrl($row)
    {
        return 'javascript:void(0)';
    }
}