<?php

class Vikont_Pulliver_Model_Catalogsearch_Layer extends Mage_CatalogSearch_Model_Layer
{

	public function prepareProductCollection($collection)
	{
		parent::prepareProductCollection($collection);

		$query = Mage::helper('catalogsearch')->getQuery();
		$queryText = trim($query->getQueryText());

		do {
			$temp = $queryText;
			$queryText = str_replace('  ', ' ', $queryText);
		} while ($temp != $queryText);

//		$words = addslashes(str_replace(' ', ',', $queryText));
		$words = explode(' ', $queryText);
		$joinCondition = array();

		foreach(Vikont_Pulliver_Helper_Sku::getDistributorFieldNames() as $dFieldName) {
			foreach($words as $word) {
				$joinCondition[] = "pn.d_$dFieldName='".addslashes($word)."'";
			}
		}
/*
		$collection->getSelect()
			->joinLeft(
				array('pn' => Mage::getSingleton('core/resource')->getTableName('oemdb/sku')),
				'e.sku = pn.sku AND (' . implode(' OR ', $joinCondition) . ')',
				null,
				(string) Mage::getConfig()->getNode('global/resources/oemdb_database/connection/dbname')
			);
 /**/
//sql($collection);
//die;
		return $this;
	}
}