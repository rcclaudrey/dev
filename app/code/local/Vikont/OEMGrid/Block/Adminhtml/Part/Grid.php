<?php

class Vikont_OEMGrid_Block_Adminhtml_Part_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
		$this->setTemplate('oemgrid/grid.phtml');
        $this->setId('oemgrid');
//        $this->setDefaultSort('part_number');
//        $this->setDefaultDir('ASC');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
		$this->setIndexColumn('id');
    }



    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('oemgrid/part_collection');
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }



    protected function _prepareColumns()
    {
		$store = Mage::app()->getStore(0);

		$this->addColumn('supplier_code', array(
            'index'     => 'supplier_code',
            'header'    => $this->__('Brand'),
			'type'		=> 'options',
			'options'	=> Mage::getSingleton('oemgrid/source_brand_shortcode')->getOptionArray(),
			'width'     => '100px',
			'is_editable'	=> true,
        ));

		$this->addColumn('part_number', array(
            'index'     => 'part_number',
            'header'    => $this->__('Part #'),
			'is_editable'	=> true,
			'validate' => 'required-entry',
        ));

        $this->addColumn('part_name', array(
            'index'     => 'part_name',
            'header'    => $this->__('Description'),
			'is_editable'	=> true,
			'validate' => 'required-entry',
        ));

        $this->addColumn('available', array(
            'index'     => 'available',
            'header'    => $this->__('Available'),
			'type'		=> 'options',
			'options'	=> Mage::getSingleton('adminhtml/system_config_source_yesno')->toArray(),
			'width'     => '50px',
			'align'		=> 'center',
			'is_editable'	=> true,
        ));

		// price
        $this->addColumn('cost', array(
//			'type'  => 'price',
//			'currency_code' => $store->getBaseCurrency()->getCode(),
            'index'     => 'cost',
            'header'    => $this->__('Cost'),
			'width'     => '70px',
			'align'		=> 'right',
			'is_editable'	=> true,
			'validate' => 'validate-number-gez',
        ));

        $this->addColumn('msrp', array(
//			'type'  => 'price',
//			'currency_code' => $store->getBaseCurrency()->getCode(),
            'index'     => 'msrp',
            'header'    => $this->__('MSRP'),
			'width'     => '70px',
			'align'		=> 'right',
			'is_editable'	=> true,
			'validate' => 'validate-number-gez',
        ));

        $this->addColumn('price', array(
//			'type'  => 'price',
//			'currency_code' => $store->getBaseCurrency()->getCode(),
            'index'     => 'price',
            'header'    => $this->__('Price'),
			'width'     => '70px',
			'align'		=> 'right',
			'is_editable'	=> true,
			'validate' => 'validate-number-gez',
        ));

        $this->addColumn('hide_price', array(
            'index'     => 'hide_price',
            'header'    => $this->__('Hide price'),
			'type'		=> 'options',
			'options'	=> Mage::getSingleton('adminhtml/system_config_source_yesno')->toArray(),
			'width'     => '50px',
			'align'		=> 'center',
			'is_editable'	=> true,
        ));

		// inventory
        $this->addColumn('inv_local', array(
            'index'     => 'inv_local',
            'header'    => $this->__('Local stock'),
			'width'     => '60px',
			'align'		=> 'right',
//			'filter'	=> 'adminhtml/widget_grid_column_filter_range',
			'is_editable'	=> true,
			'validate' => 'validate-int validate-number-gez',
        ));

        $this->addColumn('inv_wh', array(
            'index'     => 'inv_wh',
            'header'    => $this->__('Warehouse stock'),
			'width'     => '60px',
			'align'		=> 'right',
//			'filter'	=> 'adminhtml/widget_grid_column_filter_range',
			'is_editable'	=> true,
			'validate' => 'validate-int validate-number-gez',
        ));

		// transportation
        $this->addColumn('dim_length', array(
            'index'     => 'dim_length',
            'header'    => $this->__('Length'),
			'width'     => '80px',
			'align'		=> 'right',
			'validate' => 'validate-number-gez',
//			'filter'	=> 'adminhtml/widget_grid_column_filter_range',
			'is_editable'	=> true,
        ));

        $this->addColumn('dim_width', array(
            'index'     => 'dim_width',
            'header'    => $this->__('Width'),
			'width'     => '80px',
			'align'		=> 'right',
			'validate' => 'validate-gez',
//			'filter'	=> 'adminhtml/widget_grid_column_filter_range',
			'is_editable'	=> true,
        ));

        $this->addColumn('dim_height', array(
            'index'     => 'dim_height',
            'header'    => $this->__('Height'),
			'width'     => '80px',
			'align'		=> 'right',
			'validate'	=> 'validate-number-gez',
//			'filter'	=> 'adminhtml/widget_grid_column_filter_range',
			'is_editable'	=> true,
        ));

        $this->addColumn('oversized', array(
            'index'     => 'oversized',
            'header'    => $this->__('Oversized'),
			'type'		=> 'options',
			'options'	=> Mage::getSingleton('adminhtml/system_config_source_yesno')->toArray(),
			'width'     => '50px',
			'align'		=> 'center',
			'is_editable'	=> true,
        ));

        $this->addColumn('weight', array(
            'index'     => 'weight',
            'header'    => $this->__('Weight'),
			'width'     => '80px',
			'align'		=> 'right',
			'is_editable'	=> true,
//			'filter'	=> 'adminhtml/widget_grid_column_filter_range',
			'validate' => 'validate-number-gz',
        ));

        $this->addColumn('uom', array(
            'index'     => 'uom',
            'header'    => $this->__('Unit of measure'),
			'type'		=> 'options',
			'options'	=> Mage::getSingleton('adminhtml/system_config_source_yesno')->toArray(),
			'width'     => '50px',
			'align'		=> 'center',
			'is_editable'	=> true,
        ));

		$this->addColumn('image_url', array(
            'index'     => 'image_url',
            'header'    => $this->__('Image'),
			'width'     => '50px',
			'filter'    => false,
            'sortable'  => false,
			'renderer'	=> 'oemgrid/adminhtml_widget_grid_column_renderer_url',
			'caption' => $this->__('View'),
			'empty_text' => $this->__('Empty'),
			'html_attributes' => array(
				'onclick' => "window.open(this.href, '_blank', 'menubar=no,toolbar=no,width=930px,scrollbars=yes')",
//				'class' => '',
//				'target' => '_blank',
			),
        ));

        $this->addColumn('delete', array(
            'type'      => 'action',
            'header'    =>  $this->__('Delete'),
            'actions'   => array(
                array(
                    'caption'   => $this->__('Delete'),
                    'url'       => array('base' => '*/*/delete'),
                    'field'     => 'id',
                    'confirm'   => $this->__('Are you sure you want to delete the selected part?'),
                ),
            ),
            'width'     => '50px',
            'getter'    => 'getId',
            'filter'    => false,
            'sortable'  => false,
            'index'     => 'id',
            'is_system' => true,
          ));

        return parent::_prepareColumns();
    }



	/*
	 * Just a wrapper for protected _prepareColumns() to call it from outside
	 */
	public function prepareColumns()
	{
		$this->_prepareColumns();
		return $this;
	}



    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid');
    }



	public function getRowId($row)
	{
		return $row->getData($this->getIndexColumn());
	}


/*
	// well, this is the most fucking up Magento developers fail ever!
	// at grid JS there is a call like: productGrid_massactionJsObject.setGridIds('231,232,233,234,...
	// that lists ALL of the fucking rows IDs, regardless of the source dataset row count
	// no wonder this shit just generates the "out of memory" errors on big datasets
	// there is no quick workaround, so I have to just keep away from using the mass action functionality
	// this is a huge fuckup of Mage decs as the PHP script just falls down without giving any notice,
	// and this is completely unpredictable as it depends on the size of the dataset being used
	// also, even working, this shitcode increases the time of response and the size of the loaded content
	// that's just another proof the grid developer was a jerk borned by a prostitute
	// I just have nothing more to say...
	protected function _prepareMassaction()
	{
		$this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('oemparts');
//        $this->getMassactionBlock()->setUseSelectAll(true);

		$this->getMassactionBlock()->addItem('delete', array(
			'label' => $this->__('Delete'),
			'url' => $this->getUrl('oemgrid/adminhtml_index/massDelete'),
			'confirm' => $this->__('Are you sure?'),
		));

		return $this;
	}
/**/
}