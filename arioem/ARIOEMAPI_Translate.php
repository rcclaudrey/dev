<?PHP

class ARIOEMAPI_Translate
{

	public function config()
	{
		return array(
			'vehicle' => array(
				'ARC' => array(
					'ATV' => 'ATV',
					'GENERATORS' => false,
					'ROV' => 'SIDE BY SIDE',
					'SNOWMOBILE' => false,
					'WATERCRAFT' => false,
				),
				'BRP' => array(
					'ROADSTER' => 'SPYDER',
					'SIDE BY SIDE' => 'SIDE BY SIDE',
					'ATV' => 'ATV',
				),
				'HOM' => array(
					'ALL-TERRAIN VEHICLE (ATV)' => 'ATV / SIDE BY SIDE',
					'MOTOR SCOOTER' => 'SCOOTER',
					'PERSONAL WATERCRAFT' => 'H2O',
					'MOTORCYCLE' => 'MOTORCYCLE',
					'FL MODELS' => false,
				),
				'HONPE' => array(
					'COMMERCIAL MOWER' => false,
					'COMPACT TRACTOR' => false,
					'GENERATOR - RV TYPE' => false,
					'LAWN MOWER' => false,
					'LAWN-TRACTOR' => false,
					'LAWN TRACTOR' => false,
					'MULTI-PURPOSE TRACTOR' => false,
					'VEHICLE-POWER CARRIER' => false,
					'POWER CARRIER' => false,
					'RIDING MOWER' =>false,
					'ROTOTILLER' => false,
					'SNOW BLOWER' => false,
					'STICK EDGER' => false,
					'TRIMMER/BRUSH CUTTER' => false,
					'WATER PUMP' => false,
				),
				'KUS' => array(
					'ATV' => 'ATV',
					'GENERATOR' => 'GENERATOR',
					'MOTORCYCLE' => 'MOTORCYCLE',
					'SIDE X SIDE' => 'SIDE BY SIDE',
					'WATERCRAFT' => 'H2O',
				),
				'POL' => array(
					'ATV' => 'ATV',
					'BOAT' => false,
					'COMMERCIAL' => false,
					'PPS' => false,
					'PWC' => false,
					'PWR' => 'GENERATOR',
					'RGR' => 'RANGER',
					'RZR' => 'RAZOR',
					'SNO' => false,
				),
				'BRP_SEA' => array(
					'SEA-DOO BOATS' => false,
					'SEA-DOO WATERCRAFT' => 'H2O',
				),
				'SUZ' => array(
					'WATER VEHICLE' => false,
					'UTILITY VEHICLE' => false,
					'MOTORCYCLE' => 'MOTORCYCLE',
					'ALL TERRAIN VEHICLE' => 'ATV',
					'SCOOTER' => 'SCOOTER',
				),
				'SLN' => array(
					'SLINGSHOT' => 'SLINGSHOT',
				),
				'VIC' => array(
					'VIC' => 'MOTORCYCLE',
				),
				'YAM' => array(
					'ALL TERRAIN VEHICLE' => 'ATV',
					'BOAT' => 'JETBOAT',
					'LAWN TRACTOR' => false,
					'MULTI-PURPOSE ENGINE' => false,
					'OUTDOOR POWER EQUIPMENT' => 'GENERATOR',
					'RACE KART' => false,
					'SIDE X SIDE' => 'SIDE BY SIDE',
					'SNOWMOBILE' => 'SNOWMOBILE',
					'WAVERUNNER' => 'H2O',
//					'MOTORCYCLE' => 'MOTORCYCLE',
//					'SCOOTER' => 'SCOOTER',
				),
			),
			'year' => array(
				'BRP' => array(
					'*ACCESSORIES' => false,
					'GENUINE PARTS_ACCESSORIES_RIDING GEAR' => false,
				),
				'HONPE' => array(
					'*' => 'All Years',
				),
			),
		);
	}

}