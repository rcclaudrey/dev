<?php

class Vikont_OEMGrid_Model_Source_Brand_Shortcode extends Vikont_OEMGrid_Model_Source_Abstract
{
	public static function getAllOptionValues()
	{
		return array_keys(self::toShortOptionArray());
	}


	public static function toShortOptionArray()
	{
		return array(
			'HO' => 'Honda',
			'HP' => 'Honda PE',
			'HW' => 'Honda W',
			'KA' => 'Kawasaki',
			'PO' => 'Polaris',
			'SD' => 'Sea-Doo',
			'SU' => 'Suzuki',
			'YA' => 'Yamaha',
		);
	}
}
