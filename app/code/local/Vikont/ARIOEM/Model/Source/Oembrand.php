<?php

class Vikont_ARIOEM_Model_Source_Oembrand extends Vikont_ARIOEM_Model_Source_Abstract
{
	protected static $_brandShortNames = array(
		'ARC' => 'arcticcat',
		'BRP' => 'canam',
		'HOM' => 'honda',
		'HONPE' => 'hondape',
		'KUS' => 'kawasaki',
		'POL' => 'polaris',
		'BRP_SEA' => 'seadoo',
		'SLN' => 'slingshot',
		'SUZ' => 'suzuki',
		'VIC' => 'victory',
		'YAM' => 'yamaha',
//		'BRP' => 'Can-Am (Bombardier)',
//		'HOM' => 'Honda',
//		'HONPE' => 'Honda Power Equipment',
//		'KUS' => 'Kawasaki',
//		'POL' => 'Polaris',
//		'BRP_SEA' => 'Sea-Doo',
//		'SLN' => 'Slingshot',
//		'SUZ' => 'Suzuki Motor of America, Inc',
//		'VIC' => 'Victory',
//		'YAM' => 'Yamaha',
	);
/*
ARC	Arctic Cat
ARN	Ariens
BBM	Bad Boy Mowers
BIL	Billy Goat
BRP	Bombardier
BRG	Briggs & Stratton
GEN	Briggs & Stratton Power Products
MUR	Briggs and Stratton  Yard Power (formerly Murry)
BRL	Brillion
BHG	Bush Hog
CUT	Cub Cadet
CUC	Cub Commercial
MCM	Dixie Chopper
DXN	Dixon
DOL	Dolmar
ECH	Echo / Shindaiwa
EVI	Evinrude
EXM	eXmark
FRS	Ferris
GIA	Giant-Vac
GRV	Gravely
GRP	Great Plains
HYP	Hayter
	
HCP	Homelite Consumer Products
HONENG	Honda Engine
HONMAR	Honda Marine
HOM	Honda Motorcycles
HONPE	Honda Power Equipment
HUS	Husqvarna Power Equipment
AYP	Husqvarna / AYP
EXC	Hustler Turf
HYG	Hydro-Gear
TTC	Jacobsen Turf Care
JDC	John Deere C & CE
EJP	Johnson
KAS	Kawasaki Construction
KWE	Kawasaki Engine
KUS	Kawasaki Vehicle/PWC
KTT	Kioti Tractor
KOH	Kohler Engines
KTM	KTM
KRC	Kuhn Krause
LPD	Land Pride
LBY	Lawn-Boy Equipment
MHD	Mahindra
MCH	McCulloch Power
	
MRC	Mercury Marine
MTD	MTD
MTP	MTD PRO
ORC	Oregon Cutting Systems Group
POL	Polaris
PLN	Poulan /Weed Eater
RED	RedMax
RDD	Ridgid
RPT	Ryobi Power Tools
SCG	Scag
TPI	Schiller Grounds Care
SCM	Schwinn Motor Scooters
BRP_SEA	Sea-Doo (Bombardier)
SMP	Simplicity
BRP_SKI	Ski-Doo (Bombardier)
SNP	Snapper
SPR	Snapper Pro
STE	Stens
SUB	Subaru Industrial Power
SUZ	Suzuki
TAK	Tanaka US
TECENG	Tecumseh Power Products
TCM	Toro Commercial Equipment
TO	Toro Consumer Equipment
TIR	Toro Irrigation
TRB	Troy-Bilt
UMC	Unverferth
WLB	Walbro
WHT	White
WDS	Woods
YAM	Yamaha
YGC	Yamaha Golf Car
YAM_OUT	Yamaha Outboard
/**/

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


	public static function getShortBrandName($brandCode)
	{
		$brandCode = strtoupper($brandCode);

		return isset(self::$_brandShortNames[$brandCode])
			?	self::$_brandShortNames[$brandCode]
			:	false;
	}



	public static function getOptionCode($value)
	{
		return array_search($value, self::toShortOptionArray());
	}

}