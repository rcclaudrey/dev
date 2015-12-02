<?php

class Vikont_ARIOEM_Model_Source_Oembrand extends Vikont_ARIOEM_Model_Source_Abstract
{

	public static function getAllOptionValues()
	{
		return array_keys(self::toShortOptionArray());
	}


	public static function toShortOptionArray()
	{
		return array(
			'ARC' => 'Arctic Cat',
			'ARN' => 'Ariens',
			'AYP' => 'Husqvarna/AYP',
			'BIL' => 'Billy Goat',
			'BRG' => 'Briggs & Stratton',
			'BRL' => 'Brillion',
			'BRP' => 'Bombardier',
			'BRP_SEA' => 'Sea-Doo (Bombardier)',
			'BRP_SKI' => 'Ski-Doo (Bombardier)',
			'CUT' => 'Cub Cadet',
			'DOL' => 'Dolmar',
			'DXN' => 'Dixon',
			'ECH' => 'Echo / Shindaiwa',
			'EJP' => 'Johnson',
			'EVI' => 'Evinrude',
			'EXC' => 'Excel (incl.Hustler Turf and Big Dog)',
			'EXC' => 'Hustler Turf',
			'EXM' => 'eXmark',
			'FRS' => 'Ferris',
			'FRT' => 'Frontier',
			'GEN' => 'Briggs & Stratton Power Products',
			'GIA' => 'Giant-Vac',
			'GRP' => 'Great Plains',
			'GRV' => 'Gravely',
			'HAY' => 'Hayter Consumer',
			'HCP' => 'Homelite Consumer Products',
			'HDM' => 'Harley-Davidson',
			'HOM' => 'Honda Motorcycles',
			'HONENG' => 'Honda Engine',
			'HONMAR' => 'Honda Marine',
			'HONPE' => 'Honda Power Equipment',
			'HSB' => 'Husaberg',
			'HUS' => 'Husqvarna Power Equipment',
			'HYG' => 'Hydro-Gear',
			'HYP' => 'Hayter',
			'JDC' => 'John Deere C & CE',
			'KAS' => 'Kawasaki Construction',
			'KLP' => 'Klippo',
			'KOH' => 'Kohler Engine',
			'KRC' => 'Kuhn Krause',
			'KTM' => 'KTM',
			'KTT' => 'Kioti Tractor',
			'KUS' => 'Kawasaki Vehicle/PWC',
			'KWE' => 'Kawasaki Engine',
			'KYM' => 'Kymco',
			'LBY' => 'Lawn-Boy Equipment',
			'LPD' => 'Land Pride',
			'MCH' => 'McCulloch Power',
			'MCM' => 'Dixie Chopper',
			'MHD' => 'Mahindra',
			'MRC' => 'Mercury Marine',
			'MTD' => 'MTD',
			'MUR' => 'Briggs & Stratton Yard Power (Murry)',
			'ORC' => 'Oregon Cutting Systems Group',
			'PLN' => 'Poulan /Weed Eater',
			'POL' => 'Polaris',
			'PRO' => 'Toro Landscape',
			'RDD' => 'Ridgid',
			'RED' => 'RedMax',
			'RPT' => 'Ryobi Power Tools',
			'SCG' => 'Scag',
			'SMP' => 'Simplicity',
			'SNP' => 'Snapper',
			'SPR' => 'Snapper Pro',
			'STE' => 'Stens',
			'SUB' => 'Subaru Industrial Power',
			'SUZ' => 'Suzuki',
			'TAK' => 'Tanaka US',
			'TCM' => 'Toro Commercial Equipment',
			'TECENG' => 'Tecumseh Power Products',
			'TIR' => 'Toro Irrigation',
			'TO' => 'Toro Consumer Equipment',
			'TPI' => 'Schiller Grounds Care',
			'TRB' => 'Troy-Bilt',
			'TTC' => 'Jacobsen Turf Care',
			'UMC' => 'Unverferth',
			'VIC' => 'Victory',
			'WDS' => 'Woods',
			'WHT' => 'White',
			'WLB' => 'Walbro',
			'WRM' => 'Wright Manufacturing',
			'YAM' => 'Yamaha',
			'YAM_OUT' => 'Yamaha Outboard',
			'YGC' => 'Yamaha Golf Car',
		);
	}

}