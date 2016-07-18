<?php

class Vikont_Format
{

	protected static $_currencyFormats = array(
		'_default' => '%s',
		'USD' => '$%s',
	);


	public static function formatPrice($value, $precision = 2, $currencyCode = 'USD')
	{
		if (!isset(self::$_currencyFormats[$currencyCode])) {
			$currencyCode = '_default';
		}

		return sprintf(self::$_currencyFormats[$currencyCode], number_format($value, $precision));
	}

}

