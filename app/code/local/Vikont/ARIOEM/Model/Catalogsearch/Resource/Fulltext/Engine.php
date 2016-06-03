<?php

class Vikont_ARIOEM_Model_Catalogsearch_Resource_Fulltext_Engine
	extends Mage_CatalogSearch_Model_Resource_Fulltext_Engine
{
	protected static $_fulltextFieldSeparator = null;



	protected static function _getFulltextFieldSeparator()
	{
		if(!self::$_fulltextFieldSeparator) {
			self::$_fulltextFieldSeparator = Mage::getResourceSingleton('catalogsearch/fulltext')->getSeparator();
		}

		return self::$_fulltextFieldSeparator;
	}



	public function getPartNumbers($productIds)
	{
		if(is_array($productIds)) {
			$productIds = array_map('addslashes', $productIds);
			$condition = 'entity_id IN (' . implode(',', $productIds) . ')';
		} else {
			$condition = 'entity_id=' . (int)$productIds;
		}

		$skus = Vikont_ARIOEM_Helper_Db::getTableValues('catalog/product', array('id' => 'entity_id', 'sku'), $condition);
		$ids2skus = array();

		foreach($skus as $item) {
			$ids2skus[$item['sku']] = $item['id'];
		}

		$data = Vikont_ARIOEM_Helper_OEM::getPartNumbers(array_keys($ids2skus));
		$result = array();

		foreach($data as $item) {
			$partNumbers = array();

			foreach($item as $fieldName => $value) {
				if('sku' == $fieldName) {
					continue;
				}
				if($value) {
					$partNumbers[] = $value;
				}
			}

			if(count($partNumbers)) {
				$result[$ids2skus[$item['sku']]] = $partNumbers;
			}
		}

		return $result;
	}



	/**
     * Add entity data to fulltext search table
     *
     * @param int $entityId
     * @param int $storeId
     * @param array $index
     * @param string $entity 'product'|'cms'
     * @return Mage_CatalogSearch_Model_Resource_Fulltext_Engine
     */
    public function saveEntityIndex($entityId, $storeId, $index, $entity = 'product')
    {
		if($entity == 'product' && Vikont_ARIOEM_Helper_Data::isEnabled()) {
			$partNumbers = $this->getPartNumbers($entityId);

			if(isset($partNumbers[$entityId])) {
				$index .= self::_getFulltextFieldSeparator() . implode(self::_getFulltextFieldSeparator(), $partNumbers[$entityId]);
			}
		}

		return parent::saveEntityIndex($entityId, $storeId, $index, $entity);
    }



    /**
     * Multi add entities data to fulltext search table
     *
     * @param int $storeId
     * @param array $entityIndexes
     * @param string $entity 'product'|'cms'
     * @return Mage_CatalogSearch_Model_Resource_Fulltext_Engine
     */
    public function saveEntityIndexes($storeId, $entityIndexes, $entity = 'product')
    {
        $data    = array();
        $storeId = (int)$storeId;
        foreach ($entityIndexes as $entityId => $index) {
            $data[] = array(
                'product_id'    => (int)$entityId,
                'store_id'      => $storeId,
                'data_index'    => $index
            );
        }

        if ($data) {
			if($entity == 'product' && Vikont_ARIOEM_Helper_Data::isEnabled()) {
				$partNumbers = $this->getPartNumbers(array_keys($entityIndexes));

				foreach($data as $key => &$value) {
					if(isset($partNumbers[$value['product_id']])) {
						$value['data_index'] .= self::_getFulltextFieldSeparator()
							. implode(self::_getFulltextFieldSeparator(), $partNumbers[$value['product_id']]);
					}
				}
			}

			Mage::getResourceHelper('catalogsearch')
                ->insertOnDuplicate($this->getMainTable(), $data, array('data_index'));
        }

        return $this;
    }

}