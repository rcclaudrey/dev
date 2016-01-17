<?php

class Vikont_EVOConnector_Model_Source_Shippingmethods
{
	protected static $_methods = array(
		'UPS' => array(
			'2DA' => '2ND DAY AIR',
			'1DA' => 'NEXT DAY AIR',
			'GND' => 'GROUND',
//			'GND' => 'FREE', // for orders less than $89
//			'' => '',
		),
	);


	public static function getShippingmethods()
	{
		return self::$_methods;
	}


	public static function getShippingMethodMapped($carrier = 'UPS', $method = 'FREE')
	{
		if(isset(self::$_methods[$carrier][$method])) {
			return self::$_methods[$carrier][$method];
		} else {
			return null;
		}
	}


}