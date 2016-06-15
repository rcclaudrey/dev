<?php

class ARIOEMAPI_Config
{

	public function config()
	{
		$config = array(
			'ari' => array(
				'api' => array(
					'key' => 'XoSR2OE5yxF687wvrLPD',
					'stream_endpoint' => 'https://partstream.arinet.com',
				),
				'image' => array(
					'directory' => 'media/oem/',  // this should end with a directory separator: /
					'file_extension' => '.gif',
					'original_file_directory' => 'orig/', // this must end with a directory separator: /
				),
				'cache' => array(
					'enabled' => false,
					'directory' => 'var/oemcache',
				),
			),
			'SITE_ROOT' => dirname(__DIR__),
			'MAIN_DB' => array(
				'host' => 'localhost',
				'name' => 'devtms_magento_v5',
				'user' => 'devtms_admin',
				'password' => 'skey2Coll!#',
			),
			'OEM_DB' => array(
				'host' => 'localhost',
				'name' => 'devtms_oem',
				'user' => 'devtms_admin',
				'password' => 'skey2Coll!#',
			),
			'session' => array(
				'use' => 'db', // 'files'
			),
//			'stock_labels' => array(
//				0 => 'Usually ships in 1-3 business days',
//				1 => 'In stock',
//			),
		);

		if($_SERVER['SERVER_NAME'] == 'dev.tmsparts.com') {
			$config['MAIN_DB'] = array(
				'host' => 'localhost',
				'name' => 'devtmspa_mag',
				'user' => 'devtmspa_user',
				'password' => 'hN0Abd5MVu~w',
			);
			$config['OEM_DB'] = array(
				'host' => 'localhost',
				'name' => 'devtmspa_oem',
				'user' => 'devtmspa_user',
				'password' => 'hN0Abd5MVu~w',
			);
		}

		return $config;
	}

}
