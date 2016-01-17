<?php

class Vikont_Pulliver_Helper_Sku extends Mage_Core_Helper_Abstract {

    protected static $_itemNumbers2ids = null;
    protected static $_skus2ids = null;
    protected static $_sku2ItemNumber = array();

	protected static $_distributorFieldNames = array(
        'PU' => 'punlim',
        'TR' => 'trocky',
        'WP' => 'wpower',
        'PO' => 'polaris',
        'SD' => 'canam',
        'FX' => 'fox',
        'HH' => 'hhouse',
        'HO' => 'honda',
        'KA' => 'kawasaki',
        'SD' => 'seadoo',
        'SU' => 'suzuki',
        'YA' => 'yamaha',
        'TL' => 'troylee',
        'OK' => 'oakley',
        'MT' => 'motonation',
        'LB' => 'leatt',
		'BL' => 'bellhelm',
    );

	protected static $supplierCodes = array(
        'canam' => 'BRP',
        'honda' => 'HOM',
        'honda' => 'HONPE',
        'honda' => 'HW',
        'polaris' => 'POL',
        'suzuki' => 'SUZ',
        'yamaha' => 'YAM',
        'kawasaki' => 'KUS'
    );



    public static function getDistributorFieldNames() {
        return self::$_distributorFieldNames;
    }



    public static function loadMagentoIds($attributeCode) {
        $attribute = Mage::getSingleton('eav/config')->getAttribute(Mage_Catalog_Model_Product::ENTITY, $attributeCode);
        $values = Vikont_Pulliver_Helper_Db::getTableValues(
                        $attribute->getBackendTable(), array(
                    'value',
                    'entity_id',
                        ), array(
                    'store_id=0',
                    'attribute_id=' . $attribute->getAttributeId()
                        ), 10
        );
        self::$_itemNumbers2ids = array();
        foreach ($values as $value) {
            self::$_itemNumbers2ids[$value['entity_id']] = $value['value'];
        }
        unset($values);
        $values = Vikont_Pulliver_Helper_Db::getTableValues(
                        'catalog/product', array('entity_id', 'sku')
        );
        self::$_skus2ids = array();
        foreach ($values as $value) {
            self::$_skus2ids[$value['entity_id']] = $value['sku'];
        }
        unset($values);
    }



    public static function getIdBySku($sku) {
        if (!self::$_skus2ids) {
            self::loadMagentoIds('sku');
        }
        return array_search($sku, self::$_skus2ids);
    }



    public static function getSkuById($id) {
        if (!self::$_skus2ids) {
            self::loadMagentoIds('sku');
        }
        if (isset(self::$_skus2ids[$itemNumber])) {
            return self::$_skus2ids[$itemNumber];
        }
        return null;
    }



    public static function getIdByItemNumber($itemNumber) {
        if (!self::$_itemNumbers2ids) {
            self::loadMagentoIds('sku');
        }
        if (isset(self::$_itemNumbers2ids[$itemNumber])) {
            return self::$_itemNumbers2ids[$itemNumber];
        }
        return null;
    }



    public static function loadDistributorParts($distributorID) {
        $resource = Mage::getSingleton('core/resource');
        $connection = $resource->getConnection('oemdb_read');
        $sql = 'SELECT `d_' . addslashes($distributorID) . '`, sku FROM ' . $resource->getTableName('oemdb/sku')
                . ' WHERE `d_' . addslashes($distributorID) . '`<>""';
        try {
            self::$_sku2ItemNumber[$distributorID] = $connection->fetchPairs($sql);
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }



    public static function getSkuByItemNumber($distributorID, $itemNumber) {
        if (!in_array($distributorID, self::$_distributorFieldNames) && isset(self::$_distributorFieldNames[$distributorID])
        ) {
            $distributorID = self::$_distributorFieldNames[$distributorID];
        } else {
            return false;
        }
        if (!isset(self::$_sku2ItemNumber[$distributorID])) {
            self::loadDistributorParts($distributorID);
        }
        return isset(self::$_sku2ItemNumber[$distributorID][$itemNumber]) ? self::$_sku2ItemNumber[$distributorID][$itemNumber] : false;
    }



    public function updateOEMtable($data) {
        $resource = Mage::getSingleton('core/resource');
        $connection = $resource->getConnection('oemdb_read');
        $sql = 'UPDATE TABLE ' . $resource->getTableName('oemdb/price')
                . ' SET price="' . (float) addslashes($data['Cost'])
                . '" WHERE part_number="' . addslashes($data['PartNumber'])
                . '" AND supplier_code="' . addslashes($data['SupplierCode']) . '"';
        try {
            $connection->query($sql);
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }



    public function delete($from, $field = null, $ids = null) {
        $resource = Mage::getSingleton('core/resource');
        $connection = $resource->getConnection('oemdb_read');
        $sql = 'DELETE FROM ' . $resource->getTableName($from);
        if ($field) {
            $condition = ' WHERE ' . $field;
            if (is_array($ids)) {
                $values = '';
                foreach ($ids as $value) {
                    $values[] = addslashes($value);
                }
                $condition .= ' IN ("' . implode('","', $values) . '")';
            } elseif (is_string($ids)) {
                $condition .= '="' . addslashes($ids) . '"';
            } elseif (is_int($ids)) {
                $condition .= '=' . (int) $ids;
            }
            $sql .= $condition;
        }
        try {
            $connection->query($sql);
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }

}
