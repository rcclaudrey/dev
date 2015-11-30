<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Pgrid
 */
class Amasty_Pgrid_Block_Adminhtml_Catalog_Category_Tab_Product extends Mage_Adminhtml_Block_Catalog_Category_Tab_Product
{

    protected $_gridAttributes = array();

    protected $_attributesKey = 'categories';

    protected $_useWebsiteColumn = false;

    protected $_groupId;

    protected function _getStore()
    {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $url = $this->getUrl('adminhtml/ampgrid_attribute/index', array('attributesKey' => $this->_attributesKey));
        $this->setChild('attributes_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('ampgrid')->__('Grid Columns'),
                    'onclick'   => sprintf("pAttribute.showConfig('%s');", $url),
                    'class'     => 'task'
                ))
        );

        $this->_groupId = Mage::helper('ampgrid')->getSelectedGroupId($this->_attributesKey);
        $this->_gridAttributes = Mage::helper('ampgrid')->prepareGridAttributesCollection($this->_groupId);
    }
    
    protected function _addColumnFilterToCollection($column)
    {
        // Set custom filter for in category flag
        if ($column->getId() == 'in_category') {
       
            $productIds = $this->_getSelectedProducts();
            if (empty($productIds)) {
                $productIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('entity_id', array('in'=>$productIds));
            }
            elseif(!empty($productIds)) {
                $this->getCollection()->addFieldToFilter('entity_id', array('nin'=>$productIds));
            }
        }
        else {
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
                            $this->getCollection()->addFilter(
                                "if(stock_item.item_id IS NULL, 0 , 1)", $cond
                            );

                        } elseif ($column->getId() == 'websites') {
                            $this->getCollection()->joinField(
                                'websites',
                                'catalog/product_website',
                                'website_id',
                                'product_id=entity_id',
                                null,
                                'left'
                            );
                            $this->getCollection()->addFieldToFilter($field , $cond);
                        } else {
                            $this->getCollection()->addFieldToFilter($field , $cond);
                        }
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
    
    protected function _parentPrepareCollection(){
        
        if ($this->getCategory()->getId()) {
            $this->setDefaultFilter(array('in_category'=>1));
        }
        $collection = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('sku')
            ->addAttributeToSelect('price')
            ->addStoreFilter($this->getRequest()->getParam('store'))
            ->joinField('position',
                'catalog/category_product',
                'position',
                'product_id=entity_id',
                'category_id='.(int) $this->getRequest()->getParam('id', 0),
                'left');
        $this->setCollection($collection);

        if ($this->getCategory()->getProductsReadonly()) {
            $productIds = $this->_getSelectedProducts();
            if (empty($productIds)) {
                $productIds = 0;
            }
            $this->getCollection()->addFieldToFilter('entity_id', array('in'=>$productIds));
        }

    }
    
    public function getAttributesButtonHtml()
    {
        return $this->getChildHtml('attributes_button');
    }
    
    public function getMainButtonsHtml()
    {
        $html = parent::getMainButtonsHtml();
        $html = $this->getAttributesButtonHtml() . $html;
        return $html;
    }

    protected function _prepareCollection()
    {
        $store = $this->_getStore();
        
        $this->_parentPrepareCollection();
        
        $collection = $this->getCollection();

        $this->_prepareCollectionStandard($collection, $store);
        $this->_prepareCollectionExtra($collection, $store);

        if ($this->_gridAttributes->getSize() > 0)
        {
            foreach ($this->_gridAttributes as $attribute)
            {
                $collection->joinAttribute($attribute->getAttributeCode(), 'catalog_product/' . $attribute->getAttributeCode(), 'entity_id', null, 'left', $store->getId());
            }
        }

        $this->setCollection($collection);
        Mage_Adminhtml_Block_Widget_Grid::_prepareCollection();

        if ($this->_useWebsiteColumn) {
            $this->getCollection()->addWebsiteNamesToResult();
        }

        return $this;
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

    protected function _prepareCollectionStandard($collection, $store)
    {
        $standardColumns = Mage::getModel('ampgrid/column')->getCollectionStandard($this->_groupId);

        foreach ($standardColumns as $column) {
            /**
             * @var Amasty_Pgrid_Model_Column $column
             */
            if (!$column->isVisible()) {
                continue;
            }

            if ($store->getId()) {
                $collection->addStoreFilter($store);
                $adminStore = Mage_Core_Model_App::ADMIN_STORE_ID;
            }
            switch ($column->getCode()) {
                case 'qty':
                    if (Mage::helper('catalog')->isModuleEnabled(
                        'Mage_CatalogInventory'
                    )
                    ) {
                        $collection->joinField(
                            $column->getCode(),
                            'cataloginventory/stock_item',
                            'qty',
                            'product_id=entity_id',
                            sprintf('{{table}}.stock_id=1 %s',
                                Mage::getConfig()->getModuleConfig('Aitoc_Aitquantitymanager')->is('active', 'true')
                                    ? sprintf('AND {{table}}.website_id = %d', Mage::app()->getWebsite()->getId()) : '' ),
                            'left'
                        );
                    }
                    break;
                case 'name':
                    if ($store->getId()) {
                        $collection->joinAttribute(
                            'name',
                            'catalog_product/name',
                            'entity_id',
                            null,
                            'inner',
                            $adminStore
                        );
                        $collection->joinAttribute(
                            'custom_name',
                            'catalog_product/name',
                            'entity_id',
                            null,
                            'inner',
                            $store->getId()
                        );
                    }
                    break;
                case 'status':
                    $collection->joinAttribute(
                        'status',
                        'catalog_product/status',
                        'entity_id',
                        null,
                        'inner',
                        $store->getId()
                    );
                    break;
                case 'visibility':
                    $collection->joinAttribute(
                        'visibility',
                        'catalog_product/visibility',
                        'entity_id',
                        null,
                        'inner',
                        $store->getId()
                    );
                    break;
                case 'price':
                    if ($store->getId()) {
                        $collection->joinAttribute(
                            'price',
                            'catalog_product/price',
                            'entity_id',
                            null,
                            'left',
                            $store->getId()
                        );
                    } else {
                        $collection->addAttributeToSelect('price');
                    }
                    break;
                case 'websites':
                    $this->_useWebsiteColumn = true;
                    break;
            }
        }
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

    protected function _prepareColumns()
    {
        if (!$this->getCategory()->getProductsReadonly()) {
            $this->addColumn('in_category', array(
                'header_css_class' => 'a-center',
                'type'      => 'checkbox',
                'name'      => 'in_category',
                'values'    => $this->_getSelectedProducts(),
                'align'     => 'center',
                'index'     => 'entity_id'
            ));
        }

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
                    if (method_exists($this, "addColumnAfter")) {
                        $this->addColumnAfter('thumb',
                            array(
                                'header'    => Mage::helper('catalog')->__('Thumbnail'),
                                'renderer'  => 'ampgrid/adminhtml_catalog_product_grid_renderer_thumb',
                                'index'		=> 'thumbnail',
                                'sortable'  => true,
                                'filter'    => false,
                                'width'     => 90,
                            ), 'entity_id');
                    } else {
                        // will add thumbnail column to be the first one
                        $this->addColumn('thumb',
                            array(
                                'header'    => Mage::helper('catalog')->__('Thumbnail'),
                                'renderer'  => 'ampgrid/adminhtml_catalog_product_grid_renderer_thumb',
                                'index'		=> 'thumbnail',
                                'sortable'  => true,
                                'filter'    => false,
                                'width'     => 90,
                            ));
                    }
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
            }
        }

        $this->_prepareColumnsStandard();

        $this->_prepareColumnsExtra();

        if ($this->_gridAttributes->getSize() > 0)
        {
            Mage::helper('ampgrid')->attachGridColumns($this, $this->_gridAttributes, $this->_getStore());
        }

        return Mage_Adminhtml_Block_Widget_Grid::_prepareColumns();
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
                                        'base'=>'adminhtml/catalog_product/edit',
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
        $this->addColumn('position', array(
            'header'    => Mage::helper('catalog')->__('Position'),
            'width'     => '1',
            'type'      => 'number',
            'index'     => 'position',
            'editable'  => !$this->getCategory()->getProductsReadonly()
            //'renderer'  => 'adminhtml/widget_grid_column_renderer_input'
        ));

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
                case 'is_in_stock':
                    if (Mage::helper('catalog')->isModuleEnabled(
                        'Mage_CatalogInventory'
                    )
                    ) {
                        $this->addColumn(
                            'is_in_stock',
                            array(
                                'header'  => Mage::helper('catalog')->__(
                                    $column->getTitle()
                                ),
                                'type'    => 'options',
                                'options' => array(0 => $this->__(
                                    'Out of stock'
                                ), 1                 => $this->__('In stock')),
                                'index'   => 'is_in_stock',
                            )
                        );
                    }
                    break;
                case 'created_at':
                    $this->addColumn(
                        'created_at', array(
                            'header' => $this->__($column->getTitle()),
                            'index'  => 'created_at',
                            'type'   => 'date',
                        )
                    );
                    break;
                case 'qty_sold':
                    $this->addColumn(
                        'qty_sold', array(
                            'header' => $this->__($column->getTitle()),
                            'index'  => 'qty_sold',
                            'type'   => 'text',
                            'width'  => "40px"
                        )
                    );
                    break;
                case 'updated_at':
                    $this->addColumn(
                        'updated_at', array(
                            'header' => $this->__($column->getTitle()),
                            'index'  => 'updated_at',
                            'type'   => 'date',
                        )
                    );
                    break;
                case 'am_special_from_date':
                    $this->addColumn(
                        'am_special_from_date', array(
                            'header' => $this->__($column->getTitle()),
                            'index'  => 'am_special_from_date',
                            'type'   => 'date',
                        )
                    );
                    break;
                case 'am_special_to_date':
                    $this->addColumn(
                        'am_special_to_date', array(
                            'header' => $this->__($column->getTitle()),
                            'index'  => 'am_special_to_date',
                            'type'   => 'date',
                        )
                    );
                    break;
                case 'related_products':
                    $this->addColumn(
                        'related_products', array(
                            'header'   => $this->__($column->getTitle()),
                            'index'    => 'related_products',
                            'sortable' => false,
                            'filter'   => false,
                            'renderer' => 'ampgrid/adminhtml_catalog_product_grid_renderer_related',
                        )
                    );
                    break;
                case 'up_sells':
                    $this->addColumn(
                        'up_sells', array(
                            'header'   => $this->__($column->getTitle()),
                            'index'    => 'up_sells',
                            'sortable' => false,
                            'filter'   => false,
                            'renderer' => 'ampgrid/adminhtml_catalog_product_grid_renderer_related',
                        )
                    );
                    break;
                case 'cross_sells':
                    $this->addColumn(
                        'cross_sells', array(
                            'header'   => $this->__($column->getTitle()),
                            'index'    => 'cross_sells',
                            'sortable' => false,
                            'filter'   => false,
                            'renderer' => 'ampgrid/adminhtml_catalog_product_grid_renderer_related',
                        )
                    );
                    break;
                case 'low_stock':
                    $this->addColumn(
                        'low_stock',
                        array(
                            'header'       => Mage::helper('catalog')->__(
                                $column->getTitle()
                            ),
                            'type'         => 'options',
                            'options'      => array("0" => $this->__('No'),
                                                    1   => $this->__('Yes')),
                            'index'        => 'low_stock',
                            'filter_index' => 'low_stock'
                        )
                    );
                    break;
            }
        }
    }
}