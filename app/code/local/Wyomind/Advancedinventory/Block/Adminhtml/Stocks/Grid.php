<?php

class Wyomind_Advancedinventory_Block_Adminhtml_Stocks_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    private $_adminStore = Mage_Core_Model_App::ADMIN_STORE_ID;

    public function __construct() {
        parent::__construct();
        $this->setId('stocksGrid');
        $this->setDefaultFilter(array('type' => "simple"));
        $this->setSaveParametersInSession(true);
    }

    protected function _getStore() {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }

    protected function _prepareCollection() {

        $store = $this->_getStore();
        $collection = Mage::getModel('catalog/product')->getCollection()
                ->addAttributeToSelect('sku')
                ->addAttributeToSelect('name')
                ->addAttributeToSelect('attribute_set_id')
                ->addAttributeToSelect('type_id');

        $collection->joinField('qty', 'cataloginventory/stock_item', 'qty', 'product_id=entity_id', '{{table}}.stock_id=1', 'left');
        $collection->joinField('stock_status', 'cataloginventory/stock_item', 'is_in_stock', 'product_id=entity_id', '{{table}}.stock_id=1', 'left');
        $collection->joinField('use_config_manage_stock', 'cataloginventory/stock_item', 'use_config_manage_stock', 'product_id=entity_id', '{{table}}.stock_id=1', 'left');
        $collection->joinField('manage_stock', 'cataloginventory/stock_item', 'manage_stock', 'product_id=entity_id', '{{table}}.stock_id=1', 'left');



        if ($store->getId() != $this->_adminStore) {
            $type = "inner";
            $condition = "manage_local_stock=1";
        } else {
            $type = "left";
            $condition = null;
        }
        $collection->joinField('multistock_enabled', 'advancedinventory/item', 'manage_local_stock', 'product_id=entity_id', $condition, $type);
        $collection->groupByAttribute(array('entity_id'));
        $collection->addStoreFilter($store);
        if ($store->getId() != $this->_adminStore)
            $stores = Mage::getModel('pointofsale/pointofsale')->getPlacesByStoreId($store->getStoreId());
        else
            $stores = Mage::getModel('pointofsale/pointofsale')->getPlaces();
        
        if (version_compare(Mage::getVersion(), '1.7.0', '<'))
            $table = '_table_';
        else
            $table = 'at_';

        foreach ($stores as $s) {
            $collection->joinField('quantity_' . $s->getPlaceId(), 'advancedinventory/stock', 'quantity_in_stock', 'product_id=entity_id', "$table" . 'quantity_' . $s->getPlaceId() . ".localstock_id = " . $table . "multistock_enabled.id AND " . "$table" . 'quantity_' . $s->getPlaceId() . ".place_id=" . $s->getPlaceId(), 'left');
        };

        $resource = Mage::getSingleton('core/resource');
        $collection->setStoreId($store->getId());
        $collection->addStoreFilter($store);
        $collection->joinAttribute('custom_name', 'catalog_product/name', 'entity_id', null, 'inner', $store->getId());
        $collection->joinAttribute('status', 'catalog_product/status', 'entity_id', null, 'inner', $store->getId());
        $collection->joinAttribute('visibility', 'catalog_product/visibility', 'entity_id', null, 'inner', $store->getId());

        $this->setCollection($collection);
        parent::_prepareCollection();

        $this->getCollection()->addWebsiteNamesToResult();
        return $this;
    }

    protected function _addColumnFilterToCollection($column) {
        if ($this->getCollection()) {
            if ($column->getId() == 'websites') {
                $this->getCollection()->joinField('websites', 'catalog/product_website', 'website_id', 'product_id=entity_id', null, 'left');
            }
        }
        return parent::_addColumnFilterToCollection($column);
    }

    protected function _prepareColumns() {
        $this->addColumn('item_id', array(
            'header' => Mage::helper('catalog')->__('ID'),
            'width' => '50px',
            'type' => 'number',
            'index' => 'entity_id',
        ));
        $this->addColumn('name', array(
            'header' => Mage::helper('catalog')->__('Name'),
            'index' => 'name',
        ));
        if ($this->_getStore()->getId() != $this->_adminStore) {
            $this->addColumn('custom_name', array(
                'header' => Mage::helper('catalog')->__('Name in %s', $this->_getStore()->getName()),
                'index' => 'custom_name',
            ));
        }
        $this->addColumn('type', array(
            'header' => Mage::helper('catalog')->__('Type'),
            'width' => '60px',
            'index' => 'type_id',
            'type' => 'options',
            'options' => Mage::getSingleton('catalog/product_type')->getOptionArray(),
        ));
        $this->addColumn('name', array(
            'header' => Mage::helper('catalog')->__('Name'),
            'index' => 'name',
        ));
        $this->addColumn('type', array(
            'header' => Mage::helper('catalog')->__('Type'),
            'width' => '100px',
            'index' => 'type_id',
            'type' => 'options',
            'options' => Mage::getSingleton('catalog/product_type')->getOptionArray(),
        ));
        $sets = Mage::getResourceModel('eav/entity_attribute_set_collection')
                ->setEntityTypeFilter(Mage::getModel('catalog/product')->getResource()->getTypeId())
                ->load()
                ->toOptionHash();
        $this->addColumn('set_name', array(
            'header' => Mage::helper('catalog')->__('Attrib. Set Name'),
            'width' => '100px',
            'index' => 'attribute_set_id',
            'type' => 'options',
            'options' => $sets,
        ));
        $this->addColumn('sku', array(
            'header' => Mage::helper('catalog')->__('SKU'),
            'width' => '80px',
            'index' => 'sku',
        ));
        $this->addColumn('visibility', array(
            'header' => Mage::helper('catalog')->__('Visibility'),
            'width' => '70px',
            'index' => 'visibility',
            'type' => 'options',
            'options' => Mage::getModel('catalog/product_visibility')->getOptionArray(),
        ));
        $this->addColumn('status', array(
            'header' => Mage::helper('catalog')->__('Status'),
            'width' => '70px',
            'index' => 'status',
            'type' => 'options',
            'options' => Mage::getSingleton('catalog/product_status')->getOptionArray(),
        ));
        if (!Mage::app()->isSingleStoreMode() && $this->_getStore()->getId() == $this->_adminStore) {
            $this->addColumn('websites', array(
                'header' => Mage::helper('catalog')->__('Websites'),
                'width' => '100px',
                'sortable' => false,
                'index' => 'websites',
                'type' => 'options',
                'options' => Mage::getModel('core/website')->getCollection()->toOptionHash(),
            ));
        }
        $this->addColumn('stock_status', array(
            'header' => Mage::helper('catalog')->__('In stock'),
            'width' => '70px',
            'index' => 'stock_status',
            'type' => 'options',
            'renderer' => "Wyomind_Advancedinventory_Block_Adminhtml_Stocks_Renderer_StockStatus",
            'options' => array(1 => Mage::helper('catalog')->__('In stock'), 0 => Mage::helper('catalog')->__('Out of stock'))
        ));


        $permissions = Mage::helper('advancedinventory/permissions')->getUserPermissions();
        $all = $permissions->isAdmin();
        $pos = $permissions->getPos();

        if ($this->_getStore()->getId() != $this->_adminStore || !$all) {
            $this->addColumn('qty2', array(
                'header' => Mage::helper('advancedinventory')->__('Qty for ' . $this->_getStore()->getName()),
                'type' => 'number',
                'index' => 'qty2',
                'align' => 'center',
                'renderer' => "Wyomind_Advancedinventory_Block_Adminhtml_Stocks_Renderer_StoreViewQty",
                'width' => '50px',
                "filter" => false,
                "sortable" => false
            ));
        } else {
            $this->addColumn('qty', array(
                'header' => Mage::helper('advancedinventory')->__('Qty'),
                'type' => 'number',
                'index' => 'qty',
                'align' => 'center',
                'renderer' => "Wyomind_Advancedinventory_Block_Adminhtml_Stocks_Renderer_GlobalQty",
                'width' => '50px',
            ));
        }



        if ($this->_getStore()->getId() != $this->_adminStore)
            $stores = Mage::getModel('pointofsale/pointofsale')->getPlacesByStoreId($this->_getStore()->getStoreId());
        else
            $stores = Mage::getModel('pointofsale/pointofsale')->getPlaces();
        foreach ($stores as $store) {
            if ((in_array($store->getPlaceId(), $pos) || $all)) {
                $name = substr($store->getName(), 0, 20 - 3);
                $s = strrpos($name, " ");
                $name = substr($name, 0, $s) . '...';
                $this->addColumn('quantity_' . $store->getPlaceId(), array(
                    'header' => Mage::helper('catalog')->__('' . $name . ' (' . $store->getStoreCode() . ')'),
                    'type' => 'number',
                    'width' => '50px',
                    'align' => 'center',
                    'index' => 'quantity_' . $store->getPlaceId(),
                    'current_store' => $this->getRequest()->getParam('store', 0),
                    'store_id' => $store->getStoreId(),
                    'place_id' => $store->getPlaceId(),
                    'renderer' => new Wyomind_Advancedinventory_Block_Adminhtml_Stocks_Renderer_PosQty(),
                ));
            }
        };
        $this->addColumn('action', array(
            'header' => Mage::helper('catalog')->__('Action'),
            'width' => '100px',
            'align' => 'center',
            'type' => 'action',
            'filter' => false,
            'sortable' => false,
            'renderer' => 'Wyomind_Advancedinventory_Block_Adminhtml_Stocks_Renderer_Action',
        ));
        return parent::_prepareColumns();
    }

    public function getRowUrl($row) {
        return false;
    }

    protected function _prepareMassaction() {
        if ($this->_getStore()->getId() == $this->_adminStore) {
            $this->setMassactionIdField('entity_id');
            $this->getMassactionBlock()->setFormFieldName('product_id');
            $this->getMassactionBlock()->addItem('enable_multistock', array(
                'label' => Mage::helper('advancedinventory')->__('Enable multi-stock'),
                'value' => 'enableMultistock',
                'url' => $this->getUrl('*/*/MassEnable')
            ));
            $this->getMassactionBlock()->addItem('disable_multistock', array(
                'label' => Mage::helper('advancedinventory')->__('Disable multi-stock'),
                'value' => 'disableMultistock',
                'url' => $this->getUrl('*/*/MassDisable')
            ));
        }
        Mage::dispatchEvent('adminhtml_catalog_product_grid_prepare_massaction', array('block' => $this));
        return $this;
    }

}
