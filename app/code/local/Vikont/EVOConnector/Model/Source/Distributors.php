<?php

class Vikont_EVOConnector_Model_Source_Distributors
{
/*
	protected static $_distributors = array(
		1 => 'Tucker Rocky',
		2 => 'Parts Unlimited',
		3 => 'Western Powersports',
		4 => 'Arctic Cat',
		5 => 'Polaris',
		7 => 'Can-Am,CAN',
		8 => 'Fox',
		10 => 'Helmet House',
		11 => 'Honda',
		12 => 'Kawasaki',
		13 => 'KTM',
		14 => 'Sea-Doo',
		15 => 'Ski-Doo',
		16 => 'Suzuki',
		18 => 'Yamaha',
		19 => 'Marshall',
		21 => 'Sullivans',
		22 => 'Vespa',
		23 => 'Piaggio',
		27 => 'Southern Motorcycle Supply',
	);
/**/

	const SHORT_CODE = 0;
	const LONG_NAME = 1;
	const CODED_NAME = 2;


	protected static $_distributors = array(
		1 => array('TR', 'TUCKER ROCKY', 'tucker_rocky'),
		2 => array('PU', 'PARTS UNLIMITED', 'parts_unlimited'),
		3 => array('WP', 'WESTERN POWERSPORTS', 'western_powersports'),
		4 => array('PM', 'ARTIC CAT-PRESTIGE MS 951-695-2720', 'arctic_cat'),
		5 => array('PO', 'POLARIS/VICTORY', 'polaris'),
		7 => array('SD', 'CAN-AM', 'canam'),
		8 => array('FX', 'FOX HEAD, INC.', 'fox'),
		10 => array('HH', 'HELMET HOUSE', 'helmet_house'),
		11 => array('HO', 'HONDA MOTOR CORP', 'honda'),
		12 => array('KA', 'KAWASAKI MOTOR CORP.', 'kawasaki'),
		13 => array('KT', 'KTM', 'ktm'),
		14 => array('SD', 'SEA-DOO', 'seadoo'),
		16 => array('SU', 'SUZUKI MOTOR CORP', 'suzuki'),
		18 => array('YA', 'YAMAHA MOTOR CORP', 'yamaha'),
		21 => array('SB', 'SULLIVAN BRO\'S', 'sullivan_bros'),
		27 => array('ST', 'SOUTHERN MC SUPPLY', 'southern_mc_supply'),
		28 => array('KJ', 'KURYAKYN', 'kuryakyn'),
	);



	public static function getDistributors()
	{
		return self::$_distributors;
	}


	public static function getDistiributorInfo($id)
	{
		if(isset(self::$_distributors[$id])) {
			return self::$_distributors[$id];
		} else {
			return null;
		}
	}


	public static function getDistiributorInfoField($id, $field = self::SHORT_CODE)
	{
		if(isset(self::$_distributors[$id][$field])) {
			return self::$_distributors[$id][$field];
		} else {
			return null;
		}
	}


}