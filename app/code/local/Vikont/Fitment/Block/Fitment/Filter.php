<?php

class Vikont_Fitment_Block_Fitment_Filter extends Vikont_Fitment_Block_Fitment_Abstract
{
	const FACET_VALUE_QTY_LIMIT_DEFAULT = 10;



	protected function _construct()
	{
		parent::_construct();

		$this->setTemplate('vk_fitment/fitment/filter.phtml');
	}



	protected function _toHtml()
	{
		if(static::rideIsRequired()) return '';
		else return parent::_toHtml();
	}



	public function getFacets()
	{
		$facets = isset(self::$_ariData['Facets']) ? self::$_ariData['Facets'] : array();
//return $facets;
		if(!count($facets)) {
			return $facets;
		}

		$pageMode = isset(self::$_ariParams['pageMode']) ? self::$_ariParams['pageMode'] : array();
		$categoryFieldsCodes = array(
			10 => 'categoryid',
			20 => 'subcategoryid'
		);

		if('tireBySize' == $pageMode || 'tireByRide' == $pageMode) { // tireshop mode
			// TODO: check for doing the same for tireByRide mode as well!
			// TODO: make sure category ID remains at JS config when making requests

			//remove from sidebar:
			// Category, subcategory, - by field code
			// Type, Tire Application, Tire Type - by field label (called Name); detect these dynamically

			$removedFieldsNames = array(
				Vikont_Fitment_Helper_Data::normalizeCategoryName(Mage::getStoreConfig('fitment/tireshop/tiresize_attr_type')),
				Vikont_Fitment_Helper_Data::normalizeCategoryName(Mage::getStoreConfig('fitment/tireshop/tiresize_attr_tireapplication')),
				Vikont_Fitment_Helper_Data::normalizeCategoryName(Mage::getStoreConfig('fitment/tireshop/tiresize_attr_tiretype')),
			);

			foreach($facets as $facetIndex => $facet) {
				if(	in_array(strtolower($facet['Field']), $categoryFieldsCodes)
				||	in_array(Vikont_Fitment_Helper_Data::normalizeCategoryName($facet['Name']), $removedFieldsNames)
				) {
					unset($facets[$facetIndex]);
					continue;
				}
				$facets[$facetIndex]['InputType'] = 'checkbox';
			}

			return $facets;
		} elseif(!isset(self::$_ariParams['options']['categoryId'])) {
			// restricting the filters to Category only until a category is selected
			foreach($facets as $facet) {
				if(strtolower($facet['Field']) == 'categoryid') {
					$facet['InputType'] = 'select';
					return array($facet);
				}
			}
		} else { // normal workflow
			$sortedFacets = array();

			foreach($facets as $facetIndex => $facet) {
				$sortKey = array_search(strtolower($facet['Field']), $categoryFieldsCodes);

				if($sortKey) {
					$facet['InputType'] = 'select';
					$sortedFacets[$sortKey . ' '] = $facet;
				} else {
					$facet['InputType'] = 'checkbox';
					$sortedFacets[$facet['Name']] = $facet;
				}
			}

			ksort($sortedFacets);
			$facets = array();

			foreach($sortedFacets as $facet) {
				if(strtolower($facet['Field']) == 'categoryid') {
					$facets[] = array(
						'InputType' => 'resetCategory'
					);
				}
				$facets[] = $facet;
			}
		}

		return $facets;
	}



	public function getFacetValueQtyLimit()
	{
		$value = Mage::getStoreConfig('fitment/interface/facet_value_qty_limit');
		return $value ? $value : self::FACET_VALUE_QTY_LIMIT_DEFAULT;
	}



	public function getPriceFilter()
	{
		$ranges = array();
		$minPrice = 1000000000;
		$maxPrice = 0;
		$results = isset(self::$_ariData['Products']) ? self::$_ariData['Products'] : array();
		foreach($results as $product) {
			$minPrice = min($minPrice, $product['StartPrice']);
			$maxPrice = max($maxPrice, $product['StartPrice']);
		}
		$difference = $maxPrice - $minPrice;
		$rangeStep = ceil($difference / 10 );
//		if() {
//
//		}
		return $ranges;
	}

}