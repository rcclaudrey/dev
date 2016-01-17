<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Pgrid
 */
class Amasty_Pgrid_Block_Adminhtml_Catalog_Product_Grid extends Mage_Adminhtml_Block_Catalog_Product_Grid
{
    protected $_gridAttributes = array();

    protected $_groupId;

    public function __construct()
    {
        parent::__construct();
        $this->_exportPageSize = null;
    }

    protected function _preparePage()
    {
        $this->getCollection()->setPageSize((int) $this->getParam($this->getVarNameLimit(), Mage::getStoreConfig('ampgrid/general/number_of_records')));
        $this->getCollection()->setCurPage((int) $this->getParam($this->getVarNamePage(), $this->_defaultPage));
    }
    
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->setExportVisibility('true');
        $url = $this->getUrl('adminhtml/ampgrid_attribute/index');
        $this->setChild('attributes_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('ampgrid')->__('Grid Attribute Columns'),
                    'onclick'   => sprintf("pAttribute.showConfig('%s');", $url),
                    'class'     => 'task'
                ))
        );

        if (Mage::getStoreConfig('ampgrid/general/sorting'))
        {
            $this->setChild('sortcolumns_button',
                $this->getLayout()->createBlock('adminhtml/widget_button')
                    ->setData(array(
                    'label'     => Mage::helper('ampgrid')->__('Sort Columns'),
                    'onclick'   => 'pgridSortable.init();',
                    'class'     => 'task',
                    'id'        => 'pgridSortable_button',
                ))
            );
        }

        if (Mage::helper('ampgrid/mode')->isMulti())
        {
            $this->setChild('saveall_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('ampgrid')->__('Save'),
                    'onclick'   => 'peditGrid.saveAll();',
                    'class'     => 'save disabled',
                    'id'        => 'ampgrid_saveall_button'
                ))
        );
        }

        //export old setting from system.xml
        if (!Mage::getStoreConfig('ampgrid/general/exported_columns_from_system')
            && !Mage::getStoreConfig('ampgrid/general/just_installed')
        ) {
            Mage::helper('ampgrid/migratesettings')->exportOdlColumnSettings();
        } else {
            Mage::getConfig()->saveConfig('ampgrid/general/exported_columns_from_system', 1);
        }

        $this->_groupId = Mage::helper('ampgrid')->getSelectedGroupId();
        $this->_gridAttributes = Mage::helper('ampgrid')->prepareGridAttributesCollection($this->_groupId);
        
        return $this;
    }

   protected function _addColumnFilterToCollection($column)
    {
       
        if ($this->getCollection()) {
            $field = ( $column->getFilterIndex() ) ? $column->getFilterIndex() : $column->getIndex();
       
            if ($column->getFilterConditionCallback()) {
                call_user_func($column->getFilterConditionCallback(), $this->getCollection(), $column);
            } else {
                $cond = $column->getFilter()->getCondition();
                                
                if ($field && isset($cond)) {
                    if (strpos($field, 'am_attribute_') !== FALSE){
                        $attribute = str_replace('am_attribute_', '', $field);
                        
                        $this->getCollection()->addAttributeToFilter($attribute, $cond);
                    } else if ($field == "low_stock") {
                       $this->getCollection()->addFilter("if(stock_item.item_id IS NULL, 0 , 1)", $cond);
                    } else {
                        
                        parent::_addColumnFilterToCollection($column);
                    }
                }
            }
        }
        return $this;
    }
    
    protected function _setCollectionOrder($column)
    {
        $collection = $this->getCollection();
        
        if ($collection) {
            $columnIndex = $column->getFilterIndex() ?
                $column->getFilterIndex() : $column->getIndex();
        
            if (strpos($columnIndex, 'am_attribute_') !== FALSE){
                $attribute = str_replace('am_attribute_', '', $columnIndex);
                $collection->addAttributeToSort($attribute, $column->getDir());
            } else if ($columnIndex == "low_stock") {
                $collection->getSelect()->order("low_stock " . $column->getDir());
            }  else if ($columnIndex == "thumbnail") {
                $collection->addAttributeToSort($columnIndex, $column->getDir());
            } else {
                parent::_setCollectionOrder($column);
            }
        }
        return $this;
    }
    
    public function setOrder($collection, $attribute, $dir = 'desc')
    {
        if ($attribute == 'price') {
            $collection->addAttributeToSort($attribute, $dir);
        } else {
            $collection->getSelect()->order($attribute . ' ' .strtoupper($dir));
        }
        return $collection;
    }
    
    public function setCollection($collection)
    {
        $store = $this->_getStore();

        $this->_prepareCollectionExtra($collection, $store);

        if (!Mage::registry('product_collection')){
            Mage::register('product_collection', $collection);
        }

        /**
         * Adding attributes
         */
        if ($this->_gridAttributes->getSize() > 0)
        {
            foreach ($this->_gridAttributes as $attribute)
            {
                $collection->joinAttribute($attribute->getAttributeCode(), 'catalog_product/' . $attribute->getAttributeCode(), 'entity_id', null, 'left', $store->getId());
            }
        }

        return parent::setCollection($collection);
    }

    protected function _prepareCollectionExtra($collection, $store) {
        $extraColumns = Mage::getModel('ampgrid/column')->getCollectionExtra($this->_groupId);

        foreach ($extraColumns as $column) {
            /**
             * @var Amasty_Pgrid_Model_Column $column
             */
            if (!$column->isVisible()) {
                continue;
            }

            switch ($column->getCode()) {
                case 'thumb':
                    $collection->joinAttribute(
                        'thumbnail', 'catalog_product/thumbnail', 'entity_id',
                        null, 'left', $this->_getStore()->getId()
                    );
                    break;
                case 'am_special_from_date':
                    $collection->joinAttribute(
                        'am_special_from_date', 'catalog_product/special_from_date',
                        'entity_id', null, 'left', $store->getId()
                    );
                    break;
                case 'am_special_to_date':
                    $collection->joinAttribute(
                        'am_special_to_date', 'catalog_product/special_to_date',
                        'entity_id', null, 'left', $store->getId()
                    );
                    break;
                case 'low_stock':
                    $this->_addLowStockFilter($collection);
                    break;
                case 'qty_sold':
                    $qtySoldTable = Mage::getSingleton('core/resource')->getTableName('am_pgrid_qty_sold');
                    $collection->joinTable($qtySoldTable, 'product_id=entity_id',
                        array('qty_sold' => 'qty_sold'),
                        null,
                        'left'
                    );
                    break;
                case 'is_in_stock':
                    $collection->joinField('is_in_stock',
                        'cataloginventory/stock_item',
                        'is_in_stock',
                        'product_id=entity_id',
                         sprintf('{{table}}.stock_id=1 %s',
                             Mage::getConfig()->getModuleConfig('Aitoc_Aitquantitymanager')->is('active', 'true')
                                ? sprintf('AND {{table}}.website_id = %d', $store->getWebsiteId()) : '' ),
                        'left');
                    break;
            }
        }
    }
    
    protected function _addLowStockFilter($collection)
    {
        $configManageStock = (int) Mage::getStoreConfigFlag(
            Mage_CatalogInventory_Model_Stock_Item::XML_PATH_MANAGE_STOCK);
        $globalNotifyStockQty = (float) Mage::getStoreConfig(
            Mage_CatalogInventory_Model_Stock_Item::XML_PATH_NOTIFY_STOCK_QTY);
        Mage::helper('rss')->disableFlat();
        
        $stockItemWhere = '({{table}}.low_stock_date is not null) '
            . " AND ( ({{table}}.use_config_manage_stock=1 AND {$configManageStock}=1)"
            . " AND {{table}}.qty < "
            . "IF(stock_item.`use_config_notify_stock_qty`, {$globalNotifyStockQty}, {{table}}.notify_stock_qty)"
            . ' OR ({{table}}.use_config_manage_stock=0 AND {{table}}.manage_stock=1) )';

        if(Mage::getConfig()->getModuleConfig('Aitoc_Aitquantitymanager')->is('active', 'true')) {
            $stockItemWhere .=  sprintf('AND {{table}}.website_id = %d', $this->_getStore()->getWebsiteId());
        }

        $collection
            ->addAttributeToSelect('name', true)
            ->joinTable(array(
                'stock_item' => 'cataloginventory/stock_item'
                ), 'product_id=entity_id',
                array(
                     'if(stock_item.item_id IS NULL, 0 , 1) as low_stock'
                    ),
                $stockItemWhere, 'left')
            ->setOrder('low_stock_date');
    }
    
    protected function _prepareColumns()
    {
        $this->addExportType('ampgrid/adminhtml_product/exportCsv', Mage::helper('customer')->__('CSV'));
        $this->addExportType('ampgrid/adminhtml_product/exportExcel', Mage::helper('customer')->__('Excel XML'));

        $this->_prepareColumnsStandard();
        $this->_prepareColumnsExtra();

        $actionsColumn = null;
        if (isset($this->_columns['action']))
        {
            $actionsColumn = $this->_columns['action'];
            unset($this->_columns['action']);
        }

        // adding cost column
        if ($this->_gridAttributes->getSize() > 0)
        {
            Mage::register('ampgrid_grid_attributes', $this->_gridAttributes);

            Mage::helper('ampgrid')->attachGridColumns($this, $this->_gridAttributes, $this->_getStore());
        }
        
        if ($actionsColumn && !$this->_isExport)
        {
            $this->_columns['action'] = $actionsColumn;
        }

        $this->sortColumnsByDragPosition();
    }

    protected function _prepareColumnsStandard()
    {

        $standardColumns = Mage::getModel('ampgrid/column')->getCollectionStandard($this->_groupId);
        foreach ($standardColumns as $column) {
            /**
             * @var Amasty_Pgrid_Model_Column $column
             */
            if(!$column->isVisible()) {
                continue;
            }
            switch($column->getCode()) {
                case 'entity_id':
                    $this->addColumn('entity_id',
                        array(
                            'header'=> Mage::helper('catalog')->__($column->getTitle()),
                            'width' => '50px',
                            'type'  => 'number',
                            'index' => 'entity_id',
                        ));
                    break;
                case 'name':
                    $this->addColumn('name',
                        array(
                            'header'=> Mage::helper('catalog')->__($column->getTitle()),
                            'index' => 'name',
                        ));

                    $store = $this->_getStore();
                    if ($store->getId()) {
                        $this->addColumn('custom_name',
                            array(
                                'header'=> Mage::helper('catalog')->__('%s in %s', $column->getTitle(), $store->getName()),
                                'index' => 'custom_name',
                            ));
                    }
                    break;
                case 'type':
                    $this->addColumn('type',
                        array(
                            'header'=> Mage::helper('catalog')->__($column->getTitle()),
                            'width' => '60px',
                            'index' => 'type_id',
                            'type'  => 'options',
                            'options' => Mage::getSingleton('catalog/product_type')->getOptionArray(),
                        ));
                    break;
                case 'set_name':
                    $sets = Mage::getResourceModel('eav/entity_attribute_set_collection')
                                ->setEntityTypeFilter(Mage::getModel('catalog/product')->getResource()->getTypeId())
                                ->load()
                                ->toOptionHash();

                    $this->addColumn('set_name',
                        array(
                            'header'=> Mage::helper('catalog')->__($column->getTitle()),
                            'width' => '100px',
                            'index' => 'attribute_set_id',
                            'type'  => 'options',
                            'options' => $sets,
                        ));
                    break;
                case 'sku':
                    $this->addColumn('sku',
                        array(
                            'header'=> Mage::helper('catalog')->__($column->getTitle()),
                            'width' => '80px',
                            'index' => 'sku',
                        ));
                    break;

                case 'price':
                    $store = $this->_getStore();
                    $this->addColumn('price',
                        array(
                            'header'=> Mage::helper('catalog')->__($column->getTitle()),
                            'type'  => 'price',
                            'currency_code' => $store->getBaseCurrency()->getCode(),
                            'index' => 'price',
                        ));
                    break;
                case 'qty':
                    if (Mage::helper('catalog')->isModuleEnabled('Mage_CatalogInventory')) {
                        $this->addColumn('qty',
                            array(
                                'header'=> Mage::helper('catalog')->__($column->getTitle()),
                                'width' => '100px',
                                'type'  => 'number',
                                'index' => 'qty',
                            ));
                    }
                    break;
                case 'visibility':
                    $this->addColumn('visibility',
                        array(
                            'header'=> Mage::helper('catalog')->__($column->getTitle()),
                            'width' => '70px',
                            'index' => 'visibility',
                            'type'  => 'options',
                            'options' => Mage::getModel('catalog/product_visibility')->getOptionArray(),
                        ));
                    break;
                case 'status':
                    $this->addColumn('status',
                        array(
                            'header'=> Mage::helper('catalog')->__($column->getTitle()),
                            'width' => '70px',
                            'index' => 'status',
                            'type'  => 'options',
                            'options' => Mage::getSingleton('catalog/product_status')->getOptionArray(),
                        ));
                    break;

                case 'websites':
                    if (!Mage::app()->isSingleStoreMode()) {
                        $this->addColumn('websites',
                            array(
                                'header'=> Mage::helper('catalog')->__($column->getTitle()),
                                'width' => '100px',
                                'sortable'  => false,
                                'index'     => 'websites',
                                'type'      => 'options',
                                'options'   => Mage::getModel('core/website')->getCollection()->toOptionHash(),
                            ));
                    }
                    break;
                case 'action':
                    $this->addColumn('action',
                        array(
                            'header'    => Mage::helper('catalog')->__($column->getTitle()),
                            'width'     => '50px',
                            'type'      => 'action',
                            'getter'     => 'getId',
                            'actions'   => array(
                                array(
                                    'caption' => Mage::helper('catalog')->__($column->getTitle()),
                                    'url'     => array(
                                        'base'=>'*/*/edit',
                                        'params'=>array('store'=>$this->getRequest()->getParam('store'))
                                    ),
                                    'field'   => 'id'
                                )
                            ),
                            'filter'    => false,
                            'sortable'  => false,
                            'index'     => 'stores',
                        ));
                    break;
            }
        }

    }

    protected function _prepareColumnsExtra()
    {
        $extraColumns = Mage::getModel('ampgrid/column')->getCollectionExtra($this->_groupId);
        foreach ($extraColumns as $column) {
            /**
             * @var Amasty_Pgrid_Model_Column $column
             */
            if (!$column->isVisible()) {
                continue;
            }
            switch ($column->getCode()) {
                case 'thumb':
                    if (!$this->_isExport){
                        // will add thumbnail column to be the first one
                        $this->addColumn('thumb',
                            array(
                                'header'    => Mage::helper('catalog')->__($column->getTitle()),
                                'renderer'  => 'ampgrid/adminhtml_catalog_product_grid_renderer_thumb',
                                'index'		=> 'thumbnail',
                                'sortable'  => true,
                                'filter'    => false,
                                'width'     => 90,
                            ));
                    }
                    break;
                case 'categories':
                    $categoryFilter  = false;
                    $categoryOptions = array();
                    if (Mage::getStoreConfig('ampgrid/additional/category_filter'))
                    {
                        $categoryFilter = 'ampgrid/adminhtml_catalog_product_grid_filter_category';
                        $categoryOptions = Mage::helper('ampgrid/category')->getOptionsForFilter();
                    }

                    // adding categories column
                    $this->addColumn('categories',
                        array(
                            'header'    => Mage::helper('catalog')->__($column->getTitle()),
                            'index'     => 'category_id',
                            'renderer'  => 'ampgrid/adminhtml_catalog_product_grid_renderer_category',
                            'sortable'  => false,
                            'filter'    => $categoryFilter,
                            'type'      => 'options',
                            'options'   => $categoryOptions,
                        ));
                    break;
                case 'link':
                    $this->addColumn('link', array(
                        'header'        => $this->__($column->getTitle()),
                        'index'         => 'name',
                        'type'          => 'text',
                        'sortable'  => false,
                        'filter'    => false,
                        'width' => "20px",
                        'renderer'  => 'ampgrid/adminhtml_catalog_product_grid_renderer_link',
                    ));
                    break;
                case 'is_in_stock':
                    if (Mage::helper('catalog')->isModuleEnabled('Mage_CatalogInventory')) {
                        $this->addColumn('is_in_stock',
                            array(
                                'header'  => Mage::helper('catalog')->__($column->getTitle()),
                                'type'    => 'options',
                                'options' => array(0 => $this->__('Out of stock'), 1 => $this->__('In stock')),
                                'index'   => 'is_in_stock',
                            ));
                    }
                    break;
                case 'created_at':
                    $this->addColumn('created_at', array(
                        'header'        => $this->__($column->getTitle()),
                        'index'         => 'created_at',
                        'type'          => 'date',
                    ));
                    break;
                case 'qty_sold':
                    $this->addColumn('qty_sold', array(
                        'header' => $this->__($column->getTitle()),
                        'index' => 'qty_sold',
                        'type' => 'text',
                        'width' => "40px"
                    ));
                    break;
                case 'updated_at':
                    $this->addColumn('updated_at', array(
                        'header'        => $this->__($column->getTitle()),
                        'index'         => 'updated_at',
                        'type'          => 'date',
                    ));
                    break;
                case 'am_special_from_date':
                    $this->addColumn('am_special_from_date', array(
                        'header'        => $this->__($column->getTitle()),
                        'index'         => 'am_special_from_date',
                        'type'          => 'date',
                    ));
                    break;
                case 'am_special_to_date':
                    $this->addColumn('am_special_to_date', array(
                        'header'        => $this->__($column->getTitle()),
                        'index'         => 'am_special_to_date',
                        'type'          => 'date',
                    ));
                    break;
                case 'related_products':
                    $this->addColumn('related_products', array(
                        'header' => $this->__($column->getTitle()),
                        'index' => 'related_products',
                        'sortable' => false,
                        'filter' => false,
                        'renderer'  => 'ampgrid/adminhtml_catalog_product_grid_renderer_related',
                    ));
                    break;
                case 'up_sells':
                    $this->addColumn('up_sells', array(
                        'header' => $this->__($column->getTitle()),
                        'index' => 'up_sells',
                        'sortable' => false,
                        'filter' => false,
                        'renderer'  => 'ampgrid/adminhtml_catalog_product_grid_renderer_related',
                    ));
                    break;
                case 'cross_sells':
                    $this->addColumn('cross_sells', array(
                        'header' => $this->__($column->getTitle()),
                        'index' => 'cross_sells',
                        'sortable' => false,
                        'filter' => false,
                        'renderer'  => 'ampgrid/adminhtml_catalog_product_grid_renderer_related',
                    ));
                    break;
                case 'low_stock':
                    $this->addColumn('low_stock',
                        array(
                            'header'  => Mage::helper('catalog')->__($column->getTitle()),
                            'type'    => 'options',
                            'options' => array("0" => $this->__('No'), 1 => $this->__('Yes')),
                            'index'   => 'low_stock',
                            'filter_index' => 'low_stock'
                        ));
                    break;
            }
        }
    }

    public function addColumn($columnId, $column){
        
        if (isset($column['sortable']) && !isset($column['renderer']) && $column['sortable'] === FALSE){
            
            
            if (isset($column['type']) && $column['type'] == 'action'){
                $column['renderer']  = 'ampgrid/adminhtml_catalog_product_grid_renderer_action';
            }
            else if (isset($column['options'])){
                $column['renderer']  = 'ampgrid/adminhtml_catalog_product_grid_renderer_options';
            } 
        }
        
        return parent::addColumn($columnId, $column);
    }

    public function sortColumnsByDragPosition()
    {
        if (!Mage::getStoreConfig('ampgrid/general/sorting'))
        {
            return $this;
        }
        $keys = array_keys($this->_columns);

        $orderedFields = Mage::helper('ampgrid')->getSelectedSorting($this->_groupId);
        if (empty($orderedFields)) {
            return $this;
        }

        $columns = array();
        foreach ($orderedFields as $field) {
            if (array_key_exists($field,$this->_columns)) {
                $columns[$field] = $this->_columns[$field];
            }
        }
        $unsortedColumns = array_diff_assoc($keys, $orderedFields);
        foreach ($unsortedColumns as $columnsIndex) {
            $columns[$columnsIndex] = $this->_columns[$columnsIndex];
        }

        $this->_columns = $columns;
        end($this->_columns);
        $this->_lastColumnId = key($this->_columns);
        return $this;
    }
    
    public function getAttributesButtonHtml()
    {
        return $this->getChildHtml('attributes_button');
    }

    public function getSortColumnsButtonHtml()
    {
        return $this->getChildHtml('sortcolumns_button');
    }
    
    public function getSaveAllButtonHtml()
    {
        return $this->getChildHtml('saveall_button');
    }
       
    public function getMainButtonsHtml()
    {
        $html = parent::getMainButtonsHtml();
        $html = $this->getSaveAllButtonHtml() . $this->getSortColumnsButtonHtml() . $this->getAttributesButtonHtml() . $html;
        return $html;
    }
    
   protected function _prepareMassaction()
   {
        parent::_prepareMassaction();
        Mage::dispatchEvent('am_product_grid_massaction', array('grid' => $this)); 
   }
}