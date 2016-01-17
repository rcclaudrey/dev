<?php

class Vikont_EVOConnector_Helper_OEMBrand extends Mage_Core_Helper_Abstract
{

	public static function getOEMBrandCodeByName($name)
	{
		$name = strtolower($name);

		$codes = array(
			'can-am' => 'SD',
			'canam' => 'SD',
			'honda' => 'HO',
			'honda power equipment' => 'HP',
			'honda power' => 'HP',
			'honda pe' => 'HP',
			'hondape' => 'HP',
			'kawasaki' => 'KU',
			'polaris' => 'PO',
			'suzuki' => 'SU',
			'victory' => 'PO',
			'sea-doo' => 'SD',
			'seadoo' => 'SD',
			'yamaha' => 'YA',
		);

		return isset($codes[$name]) ? $codes[$name] : false;
	}

}