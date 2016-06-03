<?php

class Vikont_EVOConnector_Model_Source_Creditcardtypes
{
	protected static $_cc = array(
		'AE' => 'AMEX',
		'VI' => 'VISA',
		'MC' => 'MASTERCARD',
		'DI' => 'DISCOVER',
		'OT' => 'OTHER',
//		'SM' => 'OTHER',
//		'SO' => 'OTHER',
//		'JCB' => 'OTHER',
//PAYPAL
	);


	public static function getCreditCardsTypes()
	{
		return self::$_cc;
	}


	public static function getCCById($id)
	{
		if(isset(self::$_cc[$id])) {
			return self::$_cc[$id];
		} else {
			return $id;
		}
	}


}