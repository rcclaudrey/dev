<?php

class Vikont_Wholesale_Model_Source_Oembrand extends Vikont_Wholesale_Model_Source_Abstract
{

	public static function getAllOptionValues()
	{
		return array_keys(self::toShortOptionArray());
	}


	public static function toShortOptionArray()
	{
		return array(
			'ARC' => 'ACat',
			'ARN' => 'Ariens',
			'AYP' => 'Husqvarna/AYP',
			'BIL' => 'Billy Goat',
			'BRG' => 'Briggs&Stratton',
			'BRL' => 'Brillion',
			'BRP' => 'Bombardier',
			'BRP_SEA' => 'SeaDoo',
			'BRP_SKI' => 'SkiDoo',
			'CUT' => 'Cub Cadet',
			'DOL' => 'Dolmar',
			'DXN' => 'Dixon',
			'ECH' => 'Echo/Shindaiwa',
			'EJP' => 'Johnson',
			'EVI' => 'Evinrude',
			'EXC' => 'Excel',
			'EXC' => 'Hustler',
			'EXM' => 'eXmark',
			'FRS' => 'Ferris',
			'FRT' => 'Frontier',
			'GEN' => 'Briggs&Stratton',
			'GIA' => 'Giant-Vac',
			'GRP' => 'GreatPlains',
			'GRV' => 'Gravely',
			'HAY' => 'Hayter',
			'HCP' => 'Homelite',
			'HDM' => 'Harley-Davidson',
			'HOM' => 'Honda',
			'HONENG' => 'HEngine',
			'HONMAR' => 'HMarine',
			'HONPE' => 'Honda PE',
			'HSB' => 'Husaberg',
			'HUS' => 'Husqvarna',
			'HYG' => 'Hydro-Gear',
			'HYP' => 'Hayter',
			'JDC' => 'JDeere',
			'KAS' => 'Kawasaki',
			'KLP' => 'Klippo',
			'KOH' => 'Kohler',
			'KRC' => 'KKrause',
			'KTM' => 'KTM',
			'KTT' => 'Kioti Tractor',
			'KUS' => 'Kawasaki',
			'KWE' => 'Kawasaki',
			'KYM' => 'Kymco',
			'LBY' => 'Lawn-Boy',
			'LPD' => 'Land Pride',
			'MCH' => 'McCulloch',
			'MCM' => 'Dixie',
			'MHD' => 'Mahindra',
			'MRC' => 'Mercury',
			'MTD' => 'MTD',
			'MUR' => 'Briggs&Stratton',
			'ORC' => 'Oregon',
			'PLN' => 'Poulan /Weed Eater',
			'POL' => 'Polaris',
			'PRO' => 'Toro',
			'RDD' => 'Ridgid',
			'RED' => 'RedMax',
			'RPT' => 'Ryobi',
			'SCG' => 'Scag',
			'SMP' => 'Simplicity',
			'SNP' => 'Snapper',
			'SPR' => 'Snapper',
			'STE' => 'Stens',
			'SUB' => 'Subaru',
			'SUZ' => 'Suzuki',
			'TAK' => 'Tanaka',
			'TCM' => 'Toro',
			'TECENG' => 'Tecumseh',
			'TIR' => 'Toro',
			'TO' => 'Toro',
			'TPI' => 'Schiller',
			'TRB' => 'TroyBilt',
			'TTC' => 'Jacobsen',
			'UMC' => 'Unverferth',
			'VIC' => 'Victory',
			'WDS' => 'Woods',
			'WHT' => 'White',
			'WLB' => 'Walbro',
			'WRM' => 'Wright',
			'YAM' => 'Yamaha',
			'YAM_OUT' => 'Yamaha',
			'YGC' => 'Yamaha',
		);
	}

}