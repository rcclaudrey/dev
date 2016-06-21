<?php


require_once("app/code/local/Wyomind/Advancedinventory/Block/Adminhtml/Catalog/Product/Grid.php");


class Vikont_Pulliver_Block_Adminhtml_Catalog_Product_Grid
	extends Wyomind_Advancedinventory_Block_Adminhtml_Catalog_Product_Grid
{

	protected function _prepareCollectionBefore()
	{
		// this comes from Mage_Adminhtml_Block_Catalog_Product_Grid
		$store = $this->_getStore();
		$collection = Mage::getModel('catalog/product')->getCollection()
			->addAttributeToSelect('sku')
			->addAttributeToSelect('name')
			->addAttributeToSelect('attribute_set_id')
			->addAttributeToSelect('type_id');

		if (Mage::helper('catalog')->isModuleEnabled('Mage_CatalogInventory')) {
			$collection->joinField('qty',
				'cataloginventory/stock_item',
				'qty',
				'product_id=entity_id',
				'{{table}}.stock_id=1',
				'left');
		}

		if ($store->getId()) {
			$adminStore = Mage_Core_Model_App::ADMIN_STORE_ID;
			$collection->addStoreFilter($store);
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
			$collection->joinAttribute(
				'status',
				'catalog_product/status',
				'entity_id',
				null,
				'inner',
				$store->getId()
			);
			$collection->joinAttribute(
				'visibility',
				'catalog_product/visibility',
				'entity_id',
				null,
				'inner',
				$store->getId()
			);
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
			$collection->joinAttribute('status', 'catalog_product/status', 'entity_id', null, 'inner');
			$collection->joinAttribute('visibility', 'catalog_product/visibility', 'entity_id', null, 'inner');
		}

		$this->setCollection($collection);


		// this came from Mage_Adminhtml_Block_Widget_Grid
		$this->_preparePage();

		$columnId = $this->getParam($this->getVarNameSort(), $this->_defaultSort);
		$dir      = $this->getParam($this->getVarNameDir(), $this->_defaultDir);
		$filter   = $this->getParam($this->getVarNameFilter(), null);

		if (is_null($filter)) {
			$filter = $this->_defaultFilter;
		}

		if (is_string($filter)) {
			$data = $this->helper('adminhtml')->prepareFilterString($filter);
			$this->_setFilterValues($data);
		} else if ($filter && is_array($filter)) {
			$this->_setFilterValues($filter);
		} else if(0 !== sizeof($this->_defaultFilter)) {
			$this->_setFilterValues($this->_defaultFilter);
		}

		if (isset($this->_columns[$columnId]) && $this->_columns[$columnId]->getIndex()) {
			$dir = (strtolower($dir)=='desc') ? 'desc' : 'asc';
			$this->_columns[$columnId]->setDir($dir);
			$this->_setCollectionOrder($this->_columns[$columnId]);
		}
	}



	protected function _prepareCollectionAfter()
	{
		// this came from Mage_Adminhtml_Block_Widget_Grid
		if (!$this->_isExport) {
			$this->getCollection()->load();
			$this->_afterLoadCollection();
		}

		// this came from Mage_Adminhtml_Block_Catalog_Product_Grid again
		$this->getCollection()->addWebsiteNamesToResult();
	}



	protected function _prepareCollection()
	{
		$this->_prepareCollectionBefore();

		$filter = $this->getParam($this->getVarNameFilter(), null);
		$data = $this->helper('adminhtml')->prepareFilterString($filter);

		if(isset($data['part_number'])) {
			$partNumber = addslashes($data['part_number']);

			$joinCondition = array();
			$extraFields = array();

			foreach(Vikont_Pulliver_Helper_Sku::getDistributorFieldNames() as $dFieldName) {
				$joinCondition[] = "pn.d_$dFieldName='$partNumber'";
				$extraFields['gd_' . $dFieldName] = new Zend_Db_Expr('GROUP_CONCAT(d_'.$dFieldName.')');
			}

			$this->getCollection()->getSelect()
				->joinInner(
					array('pn' => Mage::getSingleton('core/resource')->getTableName('oemdb/sku')),
					'e.sku = pn.sku AND (' . implode(' OR ', $joinCondition) . ')',
					$extraFields,
					(string) Mage::getConfig()->getNode('global/resources/oemdb_database/connection/dbname')
				);
		}

		$this->_prepareCollectionAfter();

		return $this;
	}



	protected function _prepareColumns()
	{
		parent::_prepareColumns();

		if(isset($this->_columns['sku'])) {
			$this->_columns['sku']
				->setWidth('120px')
				->setRenderer(Mage::app()->getLayout()
						->createBlock('pulliver/adminhtml_catalog_product_grid_column_renderer_partnumber')
							->setColumn($this->_columns['sku'])
					)
				->setFilter('pulliver/adminhtml_catalog_product_grid_column_filter_partnumber');
		}

		return $this;
	}


}