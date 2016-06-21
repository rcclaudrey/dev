<?php

class Vikont_Pulliver_Block_Adminhtml_Sku_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	protected static $_tableFields = array(
		'd_punlim' => 'Parts Unlimited',
		'd_trocky' => 'Tucker Rocky',
		'd_wpower' => 'Western Power Sports',
		'd_polaris' => 'Polaris',
		'd_canam' => 'Can Am',
		'd_fox' => 'Fox Racing',
		'd_hhouse' => 'Helmet House',
		'd_honda' => 'Honda',
		'd_kawasaki' => 'Kawasaki',
		'd_seadoo' => 'Sea Doo',
		'd_suzuki' => 'Suzuki',
		'd_yamaha' => 'Yamaha',
		'd_troylee' => 'Troy Lee',
		'd_oakley' => 'Oakley',
		'd_motonation' => 'MotoNation',
		'd_leatt' => 'Leatt',
		'd_bellhelm' => 'Bell Helmets',
	);


	protected static $_pageLimits = array(
		20 => '20',
		50 => '50',
		100 => '100',
		200 => '200',
		500 => '500',
		1000 => '1,000',
	);



	public function __construct()
	{
		parent::__construct();
		$this->setTemplate('vk_pulliver/widget/grid.phtml');
		$this->setRowClickCallback('');
		$this->setId('pulliverSkuGrid');
		$this->setDefaultSort('sku');
		$this->setDefaultDir('ASC');
		$this->setUseAjax(true);
		$this->setSaveParametersInSession(true);
		$this->setDefaultLimit(100);
	}



	protected function _prepareCollection()
	{
		$collection = Mage::getResourceModel('oemdb/sku_collection');
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}



	protected function _prepareColumns()
	{
		$this->addColumn('sku', array(
			'header'    => Mage::helper('pulliver')->__('SKU'),
			'index' => 'sku',
			'type' => 'text',
			'width' => '180px',
			'renderer' => 'pulliver/adminhtml_sku_grid_column_renderer_sku',
		));

		foreach(self::$_tableFields as $index => $name) {
			$this->addColumn($index, array(
				'header' => Mage::helper('pulliver')->__($name),
				'index' => $index,
				'type' => 'text',
			));
		}

		$this->addColumn('updated', array(
			'header'    => Mage::helper('pulliver')->__('Last Updated'),
			'index' => 'updated',
			'type' => 'datetime',
			'width' => '150px',
		));

//		$this->addColumn('action',
//			array(
//				'header'    =>  $this->__('Action'),
//				'width'     => '100',
//				'type'      => 'action',
//				'getter'    => 'getId',
//				'actions'   => array(
//					array(
//						'caption'   => Mage::helper('pulliver')->__('Delete'),
//						'url'       => array('base'=> '*/*/delete'),
//						'field'     => 'sku'
//					)
//				),
//				'filter'    => false,
//				'sortable'  => false,
//				'index'     => 'sku',
//				'is_system' => true,
//		));

		$this->addExportType('*/*/exportCsv', $this->__('CSV'));

		return parent::_prepareColumns();
	}



	public function getGridUrl()
	{
		return $this->getUrl('*/*/grid', array('_current'=> true));
	}



//	public function getRowUrl($row)
//	{
//		return $this->getUrl('*/*/edit', array('id'=>$row->getId()));
//	}



	public function getPageLimits()
	{
		return self::$_pageLimits;
	}



	public function getAbsoluteGridUrl($params = array())
	{
		return $this->getUrl('*/*/grid', $params);
	}


	protected function _prepareMassaction()
	{
		$this->setMassactionIdField('sku');
		$this->getMassactionBlock()->setFormFieldName('skus');

		$this->getMassactionBlock()->addItem('delete', array(
			 'label'    => $this->__('Delete'),
			 'url'      => $this->getUrl('*/*/massDelete'),
			 'confirm'  => $this->__('Are you sure to delete these records?'),
		));

		return $this;
	}

}
