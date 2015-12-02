<?php

/*
 * DB helper class
 *
 * @author Victor aka Vikont <vikont707@gmail.com>
 */
class Vikont_Pulliver_Helper_Db extends Mage_Core_Helper_Abstract
{
	protected static $_resource = null;
	protected static $_connection = null;



	/*
	 * Returns DB resource instance
	 */
	public static function getDbResource()
	{
		if (self::$_resource === null) {
			self::$_resource = Mage::getSingleton('core/resource');
		}
		return self::$_resource;
	}



	/*
	 * Returns DB connection instance
	 */
	protected static function getDbConnection()
	{
		if (self::$_connection === null) {
			self::$_connection = self::getDbResource()->getConnection('core_read');
		}
		return self::$_connection;
	}



    /*
     * Returns entity type ID as Mage::getModel('eav/entity_setup')->getEntityTypeId('catalog_product') produces error by some unknown reason (bug?)
     *
     * @param str $entityCode Entity code
     *
     * @return int Entity type ID
     */
    public static function getEntityTypeId($entityCode)
    {
        $resource = self::getDbResource();
		$read = self::getDbConnection();

        $select = $read->select()
            ->from($resource->getTableName('eav/entity_type'), 'entity_type_id')
            ->where('entity_type_code=?', $entityCode)
            ->limit(1);

        $result = $read->fetchOne($select);

        return $result;
    }



	/*
	 * Returns single value from table
	 *
	 * @param string @table Table name
	 * @param string @fieldName Field name
	 * @param string $conditions Sift conditions
	 *
	 * @see getTableValues()
	 */
	public static function getTableValue($table, $fieldName, $conditions = false)
	{
		$res = self::getTableValues($table, $fieldName, $conditions, 1);

		$record = isset($res[0]) ? $res[0] : null;

		if(count($record)) {
			$record = current($record);
		}

		return $record;
	}



	/*
	 * Returns a list of values from table basing on search criteria
	 *
	 * @param string $table The name of the table
	 * @param array|string $fields The list of fields
	 * @param array|string $conditions Search conditions
	 * @param array|integer $limit MySQL LIMIT clause parameters
	 */
	public static function getTableValues($table, $fields = false, $conditions = false, $limit = false)
	{
		$resource = self::getDbResource();
		$read = self::getDbConnection();

		$tableName = (false === strpos($table, '/'))
			? $table
			: $resource->getTableName($table);

		$select = $read->select()->from($tableName, '');

		if(!$fields) {
			$select->columns('*');
		} else if(is_array($fields)) {
			$select->columns($fields);
		} else if(is_string($fields)) {
			$select->columns(array($fields));
		}

		if(is_array($conditions)) {
			foreach($conditions as $condition) {
				$select->where($condition);
			}
		} else if(is_string($conditions) && $conditions) {
			$select->where($conditions);
		}

		if($limit !== false) {
			$select->limit($limit);
		}

		return $read->fetchAll($select);
	}



}
