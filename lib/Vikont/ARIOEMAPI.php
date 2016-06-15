<?php

require_once 'Functions.php';

class Vikont_ARIOEMAPI
{
	protected static $_params = null;

	protected $_warnings = array();

	protected $_config = array(
		'ari' => array(
			'api' => array(
				'key' => '',
				'stream_endpoint' => '',
			),
			'calls' => array(
				'node_children' => 'RestAPI/NodeChildren',
				'assemblyimage' => 'RestAPI/AssemblyImage',
				'assemblyinfo' => 'RestAPI/AssemblyInfo',
				'search' => array(
					'parts' => 'RestAPI/SearchParts',
					'model' => 'RestAPI/SearchModel',
					'parts_within_models' => 'RestAPI/SearchPartsWithinModel', // parts by part#* and model*
					'part_models' => 'RestAPI/SearchPartModels', // just all models
					'part_models_filtered' => 'RestAPI/SearchPartModelsFiltered', // models by full part# and model*
					'part_model_assemblies' => 'RestAPI/SearchPartModelAssemblies',
				),
				'autocomplete' => array(
					'part' => 'RestAPI/PartAutoComplete',
					'model' => 'RestAPI/ModelAutoComplete',
				),
				'requiringBase64encoding' => array(
					'RestAPI/ModelAutoComplete' => array(1),	// /{brandCode}/{modelName}/{numberOfResults}  modelName
					'RestAPI/SearchModel' => array(1),			// /{brandCode}/{model}/{page}/{pageSize} model
					'RestAPI/SearchPartModels' => array(1),		// /{brandCode}/{sku}/{page}/{pageSize} sku
					'RestAPI/PartAutoComplete' => array(1),		// /{brandCode}/{partSku}/{numberOfResults} partSku
					'RestAPI/SearchParts' => array(1),			// /{brandCode}/{search}/{page}/{pageSize} search
					'RestAPI/SearchPartsWithinModel' => array(1, 2),	// /{brandCode}/{modelSearch}/{partSearch}/{page}/{pageSize} modelSearch partSearch
					'RestAPI/SearchPartModelsFiltered' => array(1, 2),	// /{brandCode}/{sku}/{modelName}/{page}/{pageSize} sku modelName
				),
			),
			'retry' => array(
				'max_count' => 1,
				'time' => 500,
				'time_max' => 6000,
			),
			'image' => array(
				'directory' => 'media/oem/',  // this should end with a directory separator: /
				'file_extension' => '.gif',
				'original_file_directory' => '', // this must end with a directory separator: /
			),
			'cache' => array(
				'enabled' => false,
				'key_separator' => '__',
				'directory' => 'var/oemcache',
			),
			'search' => array(
				'page_size' => 50,
				'min_page_size' => 20,
				'max_page_size' => 100,
			),
			'autocomplete' => array(
				'max_res_count' => 20,
			),
		),
		'SITE_ROOT' => '',
		'MAIN_DB' => array(
			'host' => 'localhost',
			'name' => '',
			'user' => '',
			'password' => '',
		),
		'OEM_DB' => array(
			'host' => 'localhost',
			'name' => '',
			'user' => '',
			'password' => '',
		),
		'tables' => array(
			'oem_cost' => 'oem_cost',
		),
		'session' => array(
			'use' => 'files',
			'module_files' => array(
				'path' => '/var/session',
			),
			'module_db' => array(
				'use' => 'MAIN_DB',
				'table_name' => 'core_session',
				'table_prefix' => '',
			),
		),
//		'stock_labels' => array(
//			0 => 'outofstock',
//			1 => 'instock',
//		),
		'translate' => array(),
	);

	protected static $_log = null;

	protected static $_brands = array(
		'ARC'		=>	'Arctic Cat',
		'BRP'		=>	'Can-Am',
		'HOM'		=>	'Honda',
		'HONPE'		=>	'Honda Power Equipment',
		'KUS'		=>	'Kawasaki',
		'POL'		=>	'Polaris',
		'BRP_SEA'	=>	'Sea-Doo',
		'SLN'		=>	'Slingshot',
		'SUZ'		=>	'Suzuki',
		'VIC'		=>	'Victory',
		'YAM'		=>	'Yamaha',
	);

	protected static $_brandShortCodes = array(
		'arcticcat' => 'ARC',
		'canam' => 'BRP',
		'honda' => 'HOM',
		'hondape' => 'HONPE',
		'kawasaki' => 'KUS',
		'polaris' => 'POL',
		'seadoo' => 'BRP_SEA',
		'slingshot' => 'SLN',
		'suzuki' => 'SUZ',
		'victory' => 'VIC',
		'yamaha' => 'YAM',
	);

//	const CACHE_KEY_SEPARATOR = ARI_CACHE_KEY_SEPARATOR;
//	const CACHE_DIR = ARI_CACHE_DIR;



	public function Vikont_ARIOEMAPI($config)
	{
		$this->_config = mergeArray($this->_config, $config);
	}



	public function dispatch($params)
	{
		self::$_params = $params;
		$action = isset($params['action']) ? strtolower($params['action']) : 'none';
		unset($params['action']); // we don't need this to be sent to the API
		unset($params['_']); // the jQuery parameter preventing caching
		unset($params['debug']); // we don't need this to be sent to the API
		$result = array();

		switch($action) {
			case 'vehicle':
			case 'year':
			case 'model':
			case 'assembly':
				$result = $this->processAssembly($params, $action);
				break;

			case 'part':
				$result = $this->processPart($params);
				break;

			case 'image': // site.url/arioem/index.php?action=image&brandCode=YAM&parentId=232390&assemblyId=204079&width=175&resizeBy=small
				$url = $this->processImage($params);
				if(@self::$_params['debug']) vd($url);

				if($url) {
					header('Location: ' . $url);
					die;
				} else {
					header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
					die;
				}
				break;

			case 'hash':
				$result = $this->processHash($params);
				break;

			case 'state':
				$result = $this->processState($params);
				break;

			case 'search':
				$result = $this->processSearch($params);
				$result['_request'] = self::$_params;
				break;

			case 'part-model-assemblies':
				$result = $this->processPartModelAssemblies($params);
				break;

			case 'part-models':
				$result = $this->processPartModels($params);
				break;

			case 'autocomplete':
				$result = $this->processAutocomplete($params);
				break;

//			case 'test': 
//				$this->initSession();
//				vd($_SESSION);
			default: 
				return false;
		}

		if(count($this->_warnings)) {
			$result['warnings'] = $this->_warnings;
		}

		if(@self::$_params['debug'] || @self::$_params['result']) {
			vd($result);
		}

		return $result;
	}



	public function setDebugMode($value)
	{
		self::$_params['debug'] = $value;
		return $this;
	}



	public function request($requestPath, $mandatoryParams, $optionalParams = array(), $checkCache = true)
	{
		$url = $this->composeURL($requestPath, $mandatoryParams, $optionalParams);
		if(@self::$_params['debug']) { echo 'requested URL:'; vd($url); }

		if(		$checkCache
			&& ($cachedData = $this->checkCache($requestPath, $mandatoryParams, $optionalParams))
		) {
			return $cachedData;
		}

		$ch = curl_init();
		curl_setopt_array($ch, array(
			CURLOPT_URL => $url,
//			CURLOPT_VERBOSE => 0,
			CURLOPT_RETURNTRANSFER => 1,
//			CURLOPT_SSLVERSION => 3,
//			CURLOPT_SSL_VERIFYPEER => false,
//			CURLOPT_SSL_VERIFYHOST => 2,
			CURLOPT_HTTPHEADER => array(
				'applicationkey: ' . $this->_config['ari']['api']['key'],
			),
		));

		$error = false;
		$contentType = '';

		for($retry = 0; $retry < $this->_config['ari']['retry']['max_count']; $retry++) {
			$response = curl_exec($ch);

			if(@self::$_params['debug'] == 'p') { echo 'CURL response body:'; vd($response); }

			if(false === $response) {
				$error = curl_error($ch);
			} else {
				break;
			}

			if($error) {
				$sleepTime = mt_rand($this->_config['ari']['retry']['time'], $this->_config['ari']['retry']['time_max']);
				//if(@self::$_params['debug']) vd('sleep time = ' . (int)$sleepTime);
				usleep($sleepTime);
			}
		}

		if(false !== $error) {
			if(@self::$_params['debug'] == 'p') { echo 'CURL error:'; vd($error); }
			throw new Exception('CURL error: ' . $error);
		} else {
			$contentType = explode(';', curl_getinfo($ch, CURLINFO_CONTENT_TYPE));
			$contentType = isset($contentType[0]) ? trim($contentType[0]) : '';
		}

		curl_close($ch);

		if(@self::$_params['debug']) { echo 'Content-type:'; vd($contentType); }

		if(strtolower($contentType) == 'image/gif') {
			return array(
				'responseType' => 'image',
				'image' => $response,
			);
		} else if(strtolower($contentType) == 'application/json') {
			$data = json_decode($response, true);

			if(@self::$_params['debug']) vd($data);

			if(isset($data['ErrorMessage']) && $data['ErrorMessage']) {
				$errorMessage = sprintf('Error getting remote data for %s, URL: %s, Message: %s, Message Detail: %s',
						$path,
						$url,
						$data['Message'],
						isset($data['MessageDetail']) ? $data['MessageDetail'] : ''
					);
				$this->log($errorMessage);
				return null;
			}

			if($checkCache) {
				$this->saveCache($requestPath, $mandatoryParams, $optionalParams, $data);
			}
		} else { // Content-type: text/html means some error has occurred
			$this->log("ERROR: HTML content:\n $response"); // here we need to save the HTML to some log
			return null;
		}

		return $data;
	}



	public function base64EncodeModified($value)
	{
		$res = base64_encode($value);

		$res = str_replace('+', '-', $res);
		$res = str_replace('/', '_', $res);

		$equalsCount = substr_count($res, '=');
		$res = str_replace('=', '', $res) . (int)$equalsCount;

		return $res;
	}



	public function composeURL($action, $params, $optionalParams = array())
	{
		$action = trim($action, '/');
		$encodedParams = array_values($params);

		foreach($encodedParams as $index => $value)
		if (	isset($this->_config['ari']['calls']['requiringBase64encoding'][$action])
			&&	in_array($index, $this->_config['ari']['calls']['requiringBase64encoding'][$action])
		) {
			$encodedParams[$index] = $this->base64EncodeModified($value);
		} else {
			$encodedParams[$index] = urlencode($value);
		}

		return rtrim($this->_config['ari']['api']['stream_endpoint'],'/') . '/' . $action
			. (	count($encodedParams)
				?	'/' . implode('/', $encodedParams)
				:	''
			)
			. (	count($optionalParams)
				?	'?' . http_build_query($optionalParams)
				:	''
			);
	}



	public function addWarning($message)
	{
		$this->_warnings[] = $message;
		return $this;
	}



	public function saveImageFile($fileName, $data)
	{
		$imageDirPath = dirname($fileName);
		if(!file_exists($imageDirPath)) {
			mkdir($imageDirPath, 0755, true);
		}

		if ($f = fopen($fileName, 'w')) {
			fwrite($f, $data);
			fclose($f);
		} else {
			if(@self::$_params['debug']) vd("Cannot open file '$fileName' for writing");
			$this->log("Cannot open file '$fileName' for writing");
		}
	}



	public function processImage($params)
	{
		$brandCode = strtoupper($params['brandCode']);
		$parentId = (int)$params['parentId'];
		$assemblyId = (int)$params['assemblyId'];
		$dimension = isset($params['width'])
			?	(int)$params['width']
			:	0;
		$resizeBySmallerSide = (	isset($params['resizeBy'])
			&& (in_array(strtolower($params['resizeBy']), array('s', 'sm', 'small', 'smaller'))));
		unset($params['resizeBy']);

		$grabOriginalOnly = (isset($params['src']) && 'orig' == $params['src']);
		unset($params['src']);

		if(		!array_key_exists($brandCode, self::$_brands)
			||	!$parentId
			||	!$assemblyId
		) {
			return false;
		}

		$rootDir = $this->_config['SITE_ROOT'];
		// site instance root for cases like site.url/test/
		$instanceRoot = (string)substr(strstr($rootDir, $_SERVER['DOCUMENT_ROOT']), strlen($_SERVER['DOCUMENT_ROOT']) + 1);
		$fileRoute = '/' . $instanceRoot . $this->_config['ari']['image']['directory'] . ($dimension ? $dimension . ($resizeBySmallerSide ? 's' : '') : 'orig')
			. '/' . $brandCode . '/' . $parentId . '/' . $assemblyId . $this->_config['ari']['image']['file_extension']; // a path for URL
		$filePath = $rootDir . $fileRoute;
		if(@self::$_params['debug']) {
			vd('root dir = ' . $rootDir);
			vd('file dir = ' . $fileRoute);
			vd('file path = ' . $filePath);
		}

		if(!file_exists($filePath)) {
			if($grabOriginalOnly) {
				$originalFilePath = $rootDir . '/' . $instanceRoot . $this->_config['ari']['image']['directory']
					. $this->_config['ari']['image']['original_file_directory']
					. $brandCode . '/' . $parentId . '/' . $assemblyId . $this->_config['ari']['image']['file_extension'];
				if(@self::$_params['debug']) { echo 'Image of the requested size does not exist, original image path:'; vd($originalFilePath); }

				if(!file_exists($originalFilePath)) {
					$paramsForOrigImage = $params;
					unset($paramsForOrigImage['width']);
					$data = $this->request($this->_config['ari']['calls']['assemblyimage'], $paramsForOrigImage, array(), false);
					if(@self::$_params['debug']) { echo 'Downloading the original file:'; vd($data); }

					if(		isset($data['responseType'])
						&&	$data['responseType'] == 'image'
						&&	@$data['image']
					) {
						$this->saveImageFile($originalFilePath, $data['image']);
					} else {
						return false;
					}
				}

				// the original file should have been downloaded supposedly
				if(file_exists($originalFilePath)) {	// but anyway, let's better check that again!
					$origImage = imagecreatefromgif($originalFilePath);
					$origImageWidth = imagesx($origImage);
					$origImageHeight = imagesy($origImage);
					if(@self::$_params['debug']) { echo "Original image found, image dimensions: $origImageWidth x $origImageHeight";  }

					if($origImageWidth >= $origImageHeight) {
						if($resizeBySmallerSide) {
							$resizedWidth = $dimension;
							$resizedHeight = $dimension;
							$resizedStartX = floor(($origImageWidth - $origImageHeight) / 2);
							$resizedStartY = 0;
							$sourceWidth = $origImageHeight;
							$sourceHeight = $origImageHeight;
						} else {
							$resizedWidth = $dimension;
							$resizedHeight = floor($origImageHeight / $origImageWidth * $dimension);
							$resizedStartX = 0;
							$resizedStartY = 0;
							$sourceWidth = $origImageWidth;
							$sourceHeight = $origImageHeight;
						}
					} else {
						if($resizeBySmallerSide) {
							$resizedWidth = $dimension;
							$resizedHeight = $dimension;
							$resizedStartX = 0;
							$resizedStartY = floor(($origImageHeight - $origImageWidth) / 2);
							$sourceWidth = $origImageWidth;
							$sourceHeight = $origImageWidth;
						} else {
							$resizedWidth = floor($origImageWidth / $origImageHeight * $dimension);
							$resizedHeight = $dimension;
							$resizedStartX = 0;
							$resizedStartY = 0;
							$sourceWidth = $origImageWidth;
							$sourceHeight = $origImageHeight;
						}
					}
					$resizedImage = imagecreatetruecolor($resizedWidth, $resizedHeight);

					$res = imagecopyresampled(
							$resizedImage,
							$origImage,
							0, 0,
							$resizedStartX , $resizedStartY,
							$resizedWidth, $resizedHeight,
							$sourceWidth, $sourceHeight
						);

					imagedestroy($origImage);

					if(!$res) { // could not resample the image
						$this->log("could not resample the image: $originalFilePath");
						imagedestroy($resizedImage);
						imagedestroy($origImage);
						return false;
					}

					$fileDirPath = dirname($filePath);
					if(!file_exists($fileDirPath)) {
						mkdir($fileDirPath, 0755, true);
					}

					$res = imagegif($resizedImage, $filePath);
					imagedestroy($resizedImage);

					if(!$res) { // could not save resized image
						$this->log("could not save resized image: $filePath");
						imagedestroy($resizedImage);
						return false;
					}
				}
			} else { // here we grab the image as requested right from the API
				$data = $this->request($this->_config['ari']['calls']['assemblyimage'], $params, array(), false);
				if(@self::$_params['debug']) { echo 'Downloading image file:'; vd($data); }

				if(		isset($data['responseType'])
					&&	$data['responseType'] == 'image'
					&&	@$data['image']
				) {
					$this->saveImageFile($filePath, $data['image']);
				} else {
					return false;
				}
			}
		}

		$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off')
			?	'https:'
			:	'http:';

		$fileURL = $protocol . '//' . $_SERVER['SERVER_NAME'] . $fileRoute;
		if(@self::$_params['debug']) { vd($fileURL); }

		return $fileURL;
	}



	public function calcCacheKey($path, $mandatoryParams, $optionalParams)
	{
		if(count($optionalParams)) {
			ksort($optionalParams);
		}

		$longKey = $path . $this->_config['ari']['cache']['key_separator']
			. implode($this->_config['ari']['cache']['key_separator'], $mandatoryParams)
			. (	count($optionalParams)
					?	$this->_config['ari']['cache']['key_separator'] . http_build_query($optionalParams)
					:	''
			);

		return md5($longKey);
	}



	public function isCachingEnabled()
	{
		return $this->_config['ari']['cache']['enabled'];
	}



	public function getCacheFileName($cacheKey)
	{
		return $this->_config['SITE_ROOT']
			. '/' . $this->_config['ari']['cache']['directory']
			. '/' . substr($cacheKey, 0, 1) . '/' . substr($cacheKey, 1, 1)
			. '/' . $cacheKey;
	}



	public function checkCache($path, $mandatoryParams, $optionalParams)
	{
		if(!$this->isCachingEnabled()) return false;

		$cacheKey = $this->calcCacheKey($path, $mandatoryParams, $optionalParams);
		$fileName = $this->getCacheFileName($cacheKey);

		if(file_exists($fileName)) {
			if ($f = fopen($fileName, 'r')) {
				$result = fread($f, filesize($fileName));
				fclose($f);

				return json_decode($result, true);
			} else {
				if(@self::$_params['debug']) vd("Cannot open file '$fileName' for reading");
				$this->log("Cannot open file '$fileName' for reading");
			}
		}
		return false;
	}



	public function saveCache($path, $mandatoryParams, $optionalParams, $data)
	{
		if(!$this->isCachingEnabled()) return false;

		$cacheKey = $this->calcCacheKey($path, $mandatoryParams, $optionalParams);
		$fileName = $this->getCacheFileName($cacheKey);
		$fileDir = dirname($fileName);

		if(!file_exists($fileDir)) {
			mkdir($fileDir, 0755, true);
		}

		if ($f = fopen($fileName, 'w')) {
			fwrite($f, json_encode($data));
			fclose($f);
		} else {
			if(@self::$_params['debug']) vd("Cannot open file '$fileName' for writing");
			$this->log("Cannot open file '$fileName' for writing");
		}

		return true;
	}



	public function log($message)
	{
		if(!class_exists('Vikont_Log')) {
			require_once __DIR__ . '/Log.php';
			Vikont_Log::init($this->_config['SITE_ROOT'] . '/var/log/arioem.log');
		}
		Vikont_Log::log($message);
	}



	public function getOEMData($brandCode, $skus)
	{
		require_once 'DB/Deployer.php';
		$deployer = new Vikont_DB_Deployer();

		if(!$deployer->initDb(
				$this->_config['OEM_DB']['host'],
				$this->_config['OEM_DB']['user'],
				$this->_config['OEM_DB']['password'],
				$this->_config['OEM_DB']['name'])
		) {
			die('DB init not successful');
		}

		require_once $this->_config['SITE_ROOT'] . '/app/code/core/Mage/Core/Helper/Abstract.php';
		require_once $this->_config['SITE_ROOT'] . '/app/code/local/Vikont/ARIOEM/Helper/OEM.php';

		$brand = Vikont_ARIOEM_Helper_OEM::TMS2ARI($brandCode);

		if(!$brand) die('No brand found!');

		$sql = 'SELECT * FROM ' . $this->_config['tables']['oem_cost'] . ' WHERE supplier_code="' . addslashes($brand)
			. '" AND part_number IN ("' . implode('","', array_map('addslashes', $skus)) . '")';

		if(@self::$_params['debug']) sql($sql);

		if(!$result = $deployer->dbQuery($sql, 'Error running query!', 'part_number')) {
			if(@self::$_params['debug']) sql($sql);
		}

		if(@self::$_params['debug'] == 'p') vd($result);

		return $result;
	}



	public function initSession()
	{
		$sessionId = @$_COOKIE['frontend'];
		if(!$sessionId) {
			return false;
		}

		session_name('frontend');

		$moduleName = $this->_config['session']['use'];

		switch($moduleName) {
			case 'db':
				$moduleName = 'user';
// to get this managed properly, we need to register handlers for all session-related actions below:
// however, in this particular class we don't need to set any session value, so just reading that will be enough
//				session_set_save_handler(
//					array($this, 'open'),
//					array($this, 'close'),
//					array($this, 'read'),
//					array($this, 'write'),
//					array($this, 'destroy'),
//					array($this, 'gc')
//				);

				require_once 'DB/Deployer.php';
				$deployer = new Vikont_DB_Deployer();
				$dbKey = $this->_config['session']['module_db']['use'];
				if(!$deployer->initDb(
						$this->_config[$dbKey]['host'],
						$this->_config[$dbKey]['user'],
						$this->_config[$dbKey]['password'],
						$this->_config[$dbKey]['name'])
				) {
					die('DB init not successful');
				}

				$sql = 'SELECT session_data FROM '
					. $this->_config['session']['module_db']['table_prefix'] 
					. $this->_config['session']['module_db']['table_name'] 
					. ' WHERE session_id="' . addslashes($sessionId)
					. '" ORDER BY session_expires DESC LIMIT 1';

				if(@self::$_params['debug']) sql($sql);

				if(!$data = $deployer->dbQuery($sql, 'Error running query!')) {
					if(@self::$_params['debug']) sql($sql);
				}
				if(@self::$_params['debug'] == 'p') vd($data);

				if(isset($data[0]['session_data'])) {
					$_SESSION = decode_session($data[0]['session_data']);
					return true;
				}
				return false;
				break; // this command is unreachable indeed

			case 'files':
				session_save_path($this->_config['SITE_ROOT'] 
					. '/' . trim($this->_config['session']['module_files']['path'], '/'));
				break;
		}

		session_module_name($moduleName);
		return session_start();
	}



	public function convertValues($action, $params, &$data)
	{
		$brand = isset($params['brand'])
			?	$params['brand']
			:	reset($params);

		switch ($action) {
			case 'vehicle':
				foreach($data as $index => $value) {
					$key = strtoupper($value['name']);

					if (isset($this->_config['translate']['vehicle'][$brand][$key])) {
						if ($this->_config['translate']['vehicle'][$brand][$key]) {
							$data[$index]['name'] = $this->_config['translate']['vehicle'][$brand][$key];
						} else {
							unset($data[$index]);
						}
					}
				}
				$data = array_values($data);
				break;

			case 'year':
				switch ($brand) {
					case 'HONPE': // need to leave their weird names as they are
						$data = array(array(
							'id' => $params['parentId'],
							'name' => $this->_config['translate']['year']['HONPE']['*'],
						));
						break;

					case 'BRP':
					case 'BRP_SEA':
						foreach($data as $index => $value) {
							$key = strtoupper($value['name']);

							if (isset($this->_config['translate']['year']['BRP'][$key])) {
								$convertedValue = $this->_config['translate']['year']['BRP'][$key];

								if (false === $convertedValue) {
									unset($data[$index]);
								} else if ($convertedValue) {
									$data[$index]['name'] = $this->_config['translate']['year']['BRP'][$key];
								} else {
									$data[$index]['name'] = trim(str_replace(array('_', '*'), ' ', $data[$index]['name']));
								}
							}

						}
						$data = array_values($data);
						break;

					case 'SUZ':
						foreach($data as $index => $value) {
							$newValue = trim(preg_replace('/\([0-9]{4}\)/i', '', $data[$index]['name']));
							if($newValue) {
								$data[$index]['name'] = $newValue;
							}
						}
						break;

					default:
						foreach($data as $index => $value) {
							$newValue = $data[$index]['name'];
							if(ctype_digit($newValue) && ($newValue > 1910) && ($newValue < 2020)) {
								continue;
							}
							$newValue = str_replace(preg_split("/[0-9]{4}/i", $newValue), '', $newValue);
							if($newValue) {
								$data[$index]['name'] = $newValue;
							}
						}
				}
				break;

			case 'model':
				switch ($brand) {
					case 'BRP':
					case 'BRP_SEA':
						if (1 == count($data) && 'SPYDER' == strtoupper($data[0]['name'])) {
							$params['parentId'] = $data[0]['id'];

							foreach($data as $key => $value) unset($data[$key]);

							$res = $this->request($this->_config['ari']['calls']['node_children'], $params);

							if(!isset($res['Data'])) {
								throw new Exception('No Data key in result');
							}

							foreach($res['Data'] as $item) {
								$itemName = trim(rtrim(str_ireplace('SPYDER', '', $item['Name']), '0123456789'), ' ,');

								$data[] = array(
									'id' => $item['Id'],
									'name' => $itemName,
									'hash' => $this->hashName($itemName),
								);
							}
							$data = array_values($data);
						}
						break;

					case 'HOM':
						require_once 'OEM/Conversion/Honda.php';
						foreach($data as &$item) {
							$suffix = Vikont_OEM_Conversion_Honda::convert($item['name']);

							$openBracePos = strpos($item['name'], '(');
							if(false !== $openBracePos) {
								$item['name'] = trim(substr($item['name'], 0, $openBracePos));
							}

							if($suffix) {
								$item['name'] .= ' ' . $suffix;
							}
						}
						break;

					case 'HONPE':
						foreach($data as &$item) {
							$vinPos = stripos($item['name'], 'GENERATOR');
							if($vinPos) {
								$item['name'] = trim(substr($item['name'], 0, $vinPos), ' ,');
							}
						}
						break;

					case 'KUS':
					case 'POL':
					case 'VIC':
						foreach($data as &$item) {
							$firstSpacePos = strpos($item['name'], ' ');
							$openBracePos = strpos($item['name'], '(');
							if(false !== $openBracePos && false !== $openBracePos) {
								$item['name'] = trim(substr($item['name'], $firstSpacePos + 1, $openBracePos - $firstSpacePos)) . 'Model - ' . substr($item['name'], 0, $firstSpacePos) . ')';
							}
						}
						break;

					case 'SLN':
						foreach($data as &$item) {
							$openBracePos = strpos($item['name'], 'SLINGSHOT');
							if(false !== $openBracePos) {
								$item['name'] = trim(substr($item['name'], 0, $openBracePos));
							}
						}
						break;

					case 'SUZ':
						require_once 'OEM/Conversion/Suzuki.php';
						foreach($data as &$item) {
							$lastBracketPos = strrpos($item['name'], '(');
							if(false !== $lastBracketPos && is_numeric(substr($item['name'], $lastBracketPos+1, 4))) {
								$item['name'] = trim(substr($item['name'], 0, $lastBracketPos));
							}

							$suffix = Vikont_OEM_Conversion_Suzuki::convert($item['name']);
							if ($suffix) {
								$item['name'] .= ' ' . $suffix;
							}
						}
						break;

					case 'YAM':
						foreach($data as &$item) {
							$lastDashPos = strrpos($item['name'], '-');
							$year = substr($item['name'], $lastDashPos + 1);
							if(false !== $lastDashPos && $year && is_numeric($year)) {
								$item['name'] = trim(substr($item['name'], 0, $lastDashPos));
							}
						}
						break;
				}
				break;

			case 'assembly':
				switch ($brand) {
					case 'BRP_SEA':
						foreach($data as $index => $item) {
							$item['name'] = trim(preg_replace('/([^\D\s]|_)/i', '', $item['name']));
						}
						break;

					case 'SLN':
						foreach($data as $index => &$item) {
							$lastOpenBrace = strrpos($item['name'], '(');
							if(false !== $lastOpenBrace) {
								$item['name'] = trim(substr($item['name'], 0, $lastOpenBrace), ' ');
							}

							$openBracePos = strpos($item['name'], 'ALL OPTIONS');
							if(false !== $openBracePos) {
								$item['name'] = trim(substr($item['name'], 0, $openBracePos), ' -.');
							}
						}
						break;
				}
				break;
		}
		return true;
	}



	public function processAssembly($params, $action)
	{
		if(!isset($params['parentId'])) {  // parentId for the root nodes
			$params[] = -1;
		}

		$result = array(
			'parentId' => $params['parentId'],
			'res' => array()
		);

		if (isset($params['partId'])) {
			$result['partId'] = $params['partId'];
			unset($params['partId']);
		}

		$data = $this->request($this->_config['ari']['calls']['node_children'], $params);

		if(!isset($data['Data'])) {
			throw new Exception('No Data key in result');
		}

		foreach($data['Data'] as $item) {
			$result['res'][] = array(
				'id' => $item['Id'],
				'name' => $item['Name'],
//				'hash' => $this->hashName($item['Name']),
			);
		}

		$this->convertValues($action, $params, $result['res']);

		foreach($result['res'] as &$item) {
			$item['hash'] = $this->hashName($item['name']);
		}
		unset($item);

		return $result;
	}



	public function processPart($params)
	{
		$data = $this->request($this->_config['ari']['calls']['assemblyinfo'], $params);

		if(!isset($data['Data'])) {
			throw new Exception('No Data key in result');
		}

		$skus = array();
		foreach($data['Data']['Parts'] as $part) {
			$skus[] = $part['Sku'];
		}
		$oemData = $this->getOEMData($params['brandCode'], $skus);
		
		$this->initSession();
//		if(!$this->initSession()) {
//			$this->addWarning('Your session has expired. The prices shown may be different. Please relogin.');
//		}

		$isWholesale = @$_SESSION['customer_base']['is_wholesale'];
		$customerCostPercent = @$_SESSION['customer_base']['cost_percent'];

		$parts = array();
		foreach($data['Data']['Parts'] as $part) {
			$sku = $part['Sku'];

			if(isset($oemData[$sku]) && $oemData[$sku]['available']) { // if there is such record in OEM table and this part is availailable
				$retailPrice = $oemData[$sku]['price']
					?	$oemData[$sku]['price']
					:	$oemData[$sku]['msrp'];

				$price = ($isWholesale && ($customerCostPercent > 0)) // if this is is a wholesale customer
					?	round($oemData[$sku]['cost'] * (100 + $customerCostPercent) / 100, 2)
					:	$retailPrice;

				$parts[$part['SortTag']] = array(
					'available' => 1,
					'id' => $part['PartId'],
					'sku' => trim($sku),
					'name' => trim(	$oemData[$sku]['part_name']
						?	$oemData[$sku]['part_name']
						:	$part['Description'] ),
					'msrp' => ($isWholesale ? $retailPrice : $oemData[$sku]['msrp']), //$part['MSRP'],
					'price' => $price,
					'hidePrice' => $oemData[$sku]['hide_price'],
					'qty' => (int)$part['Qty'],
					'tag' => $part['Tag'], // Reference tag for this part
					'nla' => $part['NLA'], // Stands for No Longer Available (bool)
					'isSuperseded' => $part['IsSuperseded'], // If true, the original part data is prefixed by Org (bool)
					'orgSku' => $part['OrgSku'],
					'orgName' => $part['OrgDescription'],
					'orgMsrp' => $part['OrgMSRP'],
					'image' => $part['ImageUrl'], // Url for this part’s image. May be null. Only available for select catalogs
//					'invW' => (int)$oemData[$sku]['inv_wh'],
					'stockStatus' => (int)($oemData[$sku]['inv_wh'] > 0),
//					'stockLabel' =>	$this->_config['stock_labels'][(int)($oemData[$sku]['inv_wh'] > 0)],
				);
			} else {
				$parts[$part['SortTag']] = array(
					'available' => 0,
					'id' => $part['PartId'],
					'sku' => trim($sku),
					'name' => $part['Description'],
					'tag' => $part['Tag'],
				);
			}
		}
		ksort($parts); // sort items by SortTag

		$result = array(
			'imageUrl' => $data['Data']['ImageUrl'],
			'hotSpots' => $data['Data']['HotSpots'],
			'parts' => array_values($parts),
			'customerId' => (int)@$_SESSION['customer_base']['id'],
			'isWholesale' => (bool) $isWholesale,
		);

		return $result;
	}



	/*
	 * http://dev.tmsparts.com/arioem/index.php?action=hash&brand=yamaha&vehicle=scooter&year=2001&model=jog_-_cy50n&assembly=clutch&part=2VK-16620-01-00
	 */
	public function processHash($params)
	{
		$result = array(
			'hash' => http_build_query($params),
		);

		$brandHash = @$params['brand'];
		if(!$brandHash || !isset(self::$_brandShortCodes[$params['brand']])) {
			throw new Exception(sprintf('OEM HASH ERROR: brand=%s BRAND NOT FOUND', $brandHash));
		}

		$brandCode = self::$_brandShortCodes[$params['brand']];
		$result['state']['brand'] = array(
			'code' => $brandCode,
			'name' => self::$_brands[$brandCode],
			'hash' => $params['brand'],
		);

		// if modelId is specified, then this request is from part Search, not from part Selector,
		// so we must skip vehicle, year, and model selection, jumping right onto assembly step
		$pageMode = isset($params['modelId'])
			?	'search'
			:	'select';

		if ('select' == $pageMode) {
			$vehicleData = $this->processAssembly(array('brandCode' => $brandCode, 'parentId' => -1), 'vehicle');
			$result['vehicle'] = $vehicleData;

			// checking for vehicle
			$vehicleHash = @$params['vehicle'];
			if($vehicleHash) {
				$vehicleRecord = arraySearchByField($vehicleData['res'], 'hash', $vehicleHash);
				if(!$vehicleRecord) {
					throw new Exception(sprintf('OEM HASH ERROR: brand=%s (%s) vehicle=%s VEHICLE NOT FOUND',
							$brandHash, $brandCode, 
							$vehicleHash
						));
				}
				$result['state']['vehicle'] = array(
					'code' => $vehicleRecord['id'],
					'name' => $vehicleRecord['name'],
					'hash' => $vehicleHash,
				);
				$yearData = $this->processAssembly(array('brandCode' => $brandCode, 'parentId' => $vehicleRecord['id']), 'year');
				$result['year'] = $yearData;

				// checking for year
				$yearHash = @$params['year'];
				if($yearHash) {
					$yearRecord = arraySearchByField($yearData['res'], 'hash', $yearHash);
					if(!$yearRecord) {
						throw new Exception(sprintf('OEM HASH ERROR: brand=%s (%s) vehicle=%s (%s) (%d) year=%s YEAR NOT FOUND',
								$brandHash, $brandCode, 
								$vehicleHash, $vehicleRecord['name'], $vehicleRecord['id'],
								$yearHash
							));
					}
					$result['state']['year'] = array(
						'code' => $yearRecord['id'],
						'name' => $yearRecord['name'],
						'hash' => $yearHash,
					);
					$modelData = $this->processAssembly(array('brandCode' => $brandCode, 'parentId' => $yearRecord['id']), 'model');
					$result['model'] = $modelData;

					// checkig for model
					$modelHash = @$params['model'];
					if($modelHash) {
						$modelRecord = arraySearchByField($modelData['res'], 'hash', $modelHash);
						if(!$modelRecord) {
							throw new Exception(sprintf('OEM HASH ERROR: brand=%s (%s) vehicle=%s (%s) (%d) year=%s (%s) (%d) model=%s, MODEL NOT FOUND',
									$brandHash, $brandCode, 
									$vehicleHash, $vehicleRecord['name'], $vehicleRecord['id'],
									$yearHash, $yearRecord['name'], $yearRecord['id'],
									$modelHash
								));
						}
						$result['state']['model'] = array(
							'code' => $modelRecord['id'],
							'name' => $modelRecord['name'],
							'hash' => $modelHash,
						);
						$modelId = $modelRecord['id']; // this is here for compatibility with the next "common" part
					}
				}
			}
		} else if ('search' == $pageMode) {
			// checkig for model
			$modelId = (int)@$params['modelId'];
			if($modelId) {
				$modelName = @$params['modelName'];
				$modelHash = $this->hashName($modelName);

				$vehicleRecord = array(
					'id' => 'not in hash',
					'name' => '',
				);
				$yearRecord = array(
					'id' => 'not in hash',
					'name' => '',
				);
				$modelRecord = array(
					'id' => $modelId,
					'name' => $modelName,
				);

				$result['state']['vehicle'] = array(
					'code' => null,
					'name' => '',
					'hash' => '',
				);
				$result['state']['year'] = array(
					'code' => null,
					'name' => '',
					'hash' => '',
				);
				$result['state']['model'] = array(
					'code' => $modelId,
					'name' => $modelName,
					'hash' => $modelHash,
				);
			}
		}

		// now the common part goes
		if($modelHash || $modelId) {
			$assemblyData = $this->processAssembly(array('brandCode' => $brandCode, 'parentId' => $modelRecord['id']), 'assembly');
			$result['assembly'] = $assemblyData;

			$assemblyHash = @$params['assembly'];
			if($assemblyHash) {
				$assemblyRecord = arraySearchByField($assemblyData['res'], 'hash', $assemblyHash);
				if(!$assemblyRecord) {
					throw new Exception(sprintf('OEM HASH ERROR: brand=%s (%s) vehicle=%s (%s) (%d) year=%s (%d) model=%s (%s) (%d) assembly=%s, ASSEMBLY NOT FOUND',
							$brandHash, $brandCode, 
							$vehicleHash, $vehicleRecord['name'], $vehicleRecord['id'],
							$yearHash, $yearRecord['name'], $yearRecord['id'],
							$modelHash, $modelRecord['name'], $modelRecord['id'],
							$assemblyHash
						));
				}
				$result['state']['assembly'] = array(
					'code' => $assemblyRecord['id'],
					'name' => $assemblyRecord['name'],
					'hash' => $assemblyHash,
				);

				$partData = $this->processPart(array('brandCode' => $brandCode, 'parentId' => $modelRecord['id'], 'assemblyId' => $assemblyRecord['id']));
				$result['part'] = $partData;

				$partHash = @$params['part'];
				if($partHash) {
					$partRecord = arraySearchByField($partData['parts'], 'sku', $partHash);
					if($partRecord) {
						$result['state']['part'] = array(
							'sku' => $partRecord['sku'],
							'name' => $partRecord['name'],
							'tag' => $partRecord['tag'],
							'hash' => $this->hashName($partRecord['sku']),
						);
					} else {
						throw new Exception(sprintf('OEM HASH ERROR: brand=%s (%s) vehicle=%s (%s) (%d) year=%s (%s) (%d) model=%s (%s) (%d) assembly=%s (%d) part=%s, PART NOT FOUND',
								$brandHash, $brandCode, 
								$vehicleHash, $vehicleRecord['name'], $vehicleRecord['id'],
								$yearHash, $yearRecord['name'], $yearRecord['id'],
								$modelHash, $modelRecord['name'], $modelRecord['id'],
								$assemblyHash, $assemblyRecord['id'],
								$partHash
							));
					}
				}
			}
		}

		return $result;
	}



	public function hashName($value)
	{
//		return strtolower(preg_replace('/[^A-Za-z0-9\/-]/', '_', $value));
		return strtolower(trim(str_replace(array(' ', '#', '&', '=', '-'), '_', $value)));
	}



	// index.php?action=info&brand=HOM&vehicle=1031&year=2771&model=2773&assembly=69290
	public function processState($params)
	{
		$result = array();

		if(!isset($params['brand'])) {
			throw new Exception('OEM INFO ERROR: BRAND NOT SPECIFIED');
		}
		$brandCode = $params['brand'];
		$result['brand'] = array(
			'code' => $brandCode,
			'name' => self::$_brands[$brandCode],
			'hash' => array_search($brandCode, self::$_brandShortCodes),
		);

		if(!isset($params['vehicle'])) {
			throw new Exception('OEM INFO ERROR: VEHICLE NOT SPECIFIED');
		}
		$vehicleCode = $params['vehicle'];
		$vehicleData = $this->processAssembly(array('brandCode' => $brandCode, 'parentId' => -1), 'vehicle');
		if(@self::$_params['debug'] == 'p') vd($vehicleData);
		$vehicleRecord = arraySearchByField($vehicleData['res'], 'id', $vehicleCode);
		if(@self::$_params['debug'] == 'p') vd($vehicleRecord);
		if(!$vehicleRecord) {
			throw new Exception(sprintf('OEM INFO ERROR: brand=%s vehicle=%s, VEHICLE NOT FOUND',
					$brandCode, 
					$vehicleCode
				));
		}
		$result['vehicle'] = array(
			'code' => $vehicleCode,
			'name' => $vehicleRecord['name'],
			'hash' => $this->hashName($vehicleRecord['name']),
		);

		if(!isset($params['year'])) {
			throw new Exception('OEM INFO ERROR: YEAR NOT SPECIFIED');
		}
		$yearCode = $params['year'];
		$yearData = $this->processAssembly(array('brandCode' => $brandCode, 'parentId' => $vehicleCode), 'year');
		if(@self::$_params['debug'] == 'p') vd($yearData);
		$yearRecord = arraySearchByField($yearData['res'], 'id', $yearCode);
		if(@self::$_params['debug'] == 'p') vd($yearRecord);
		if(!$yearRecord) {
			throw new Exception(sprintf('OEM INFO ERROR: brand=%s vehicle=%s (%d) year=%s, YEAR NOT FOUND',
					$brandCode, 
					$vehicleRecord['name'], $vehicleRecord['id'],
					$yearCode
				));
		}
		$result['year'] = array(
			'code' => $yearCode,
			'name' => $yearRecord['name'],
			'hash' => $this->hashName($yearRecord['name']),
		);

		if(!isset($params['model'])) {
			throw new Exception('OEM INFO ERROR: MODEL NOT SPECIFIED');
		}
		$modelCode = $params['model'];
		$modelData = $this->processAssembly(array('brandCode' => $brandCode, 'parentId' => $yearCode), 'model');
		if(@self::$_params['debug'] == 'p') vd($modelData);
		$modelRecord = arraySearchByField($modelData['res'], 'id', $modelCode);
		if(@self::$_params['debug'] == 'p') vd($modelRecord);
		if(!$modelRecord) {
			throw new Exception(sprintf('OEM INFO ERROR: brand=%s vehicle=%s (%d) year=%s (%d) model=%s, MODEL NOT FOUND',
					$brandCode, 
					$vehicleRecord['name'], $vehicleRecord['id'],
					$yearRecord['name'], $yearRecord['id'],
					$vehicleCode
				));
		}
		$result['model'] = array(
			'code' => $modelRecord['id'],
			'name' => $modelRecord['name'],
			'hash' => $this->hashName($modelRecord['name']),
		);

		if(!isset($params['assembly'])) {
			throw new Exception('OEM INFO ERROR: ASSEMBLY NOT SPECIFIED');
		}
		$assemblyCode = $params['assembly'];
		$assemblyData = $this->processAssembly(array('brandCode' => $brandCode, 'parentId' => $modelCode), 'assembly');
		if(@self::$_params['debug'] == 'p') vd($assemblyData);
		$assemblyRecord = arraySearchByField($assemblyData['res'], 'id', $assemblyCode);
		if(@self::$_params['debug'] == 'p') vd($assemblyRecord);
		if(!$assemblyRecord) {
			throw new Exception(sprintf('OEM INFO ERROR: brand=%s vehicle=%s (%d) year=%s (%d) model=%s (%d) assembly=%s, ASSEMBLY NOT FOUND',
					$brandCode, 
					$vehicleRecord['name'], $vehicleRecord['id'],
					$yearRecord['name'], $yearRecord['id'],
					$modelRecord['name'], $modelRecord['id'],
					$assemblyCode
				));
		}
		$result['assembly'] = array(
			'code' => $assemblyRecord['id'],
			'name' => $assemblyRecord['name'],
			'hash' => $this->hashName($assemblyRecord['name']),
		);

		return $result;
	}



	protected function _checkPaging($data)
	{
		$result = array();

		if (isset($data['Data']['TotalNumberOfResults'])
			&& @$data['Data']['PageSize'] < $data['Data']['TotalNumberOfResults']
		) {
			$result['pager'] = array(
				'length' => $data['Data']['TotalNumberOfResults'],
				'size' => @$data['Data']['PageSize'],
				'page' => @$data['Data']['Page'],
			);
		}

		return $result;
	}



	protected function _processSearchPart($brandCode, $partNumber, $modelName = null, $paging = array(), $requestType = 'parts')
	{
		$result = array(
//			'type' => 'part',
			'res' => array(),
		);

		switch ($requestType) {
			case 'parts':
				$data = $this->request($this->_config['ari']['calls']['search']['parts'], array(
						'brandCode' => $brandCode,
						'search' => $partNumber,
					), $paging);
				break;

			case 'parts_within_models':
				$data = $this->request($this->_config['ari']['calls']['search']['parts_within_models'], array(
						'brandCode' => $brandCode,
						'modelSearch' => $modelName,
						'partSearch' => $partNumber,
					), $paging);
				break;
		}

		if(@self::$_params['debug']) { vd($data); }
		if(!isset($data['Data'])) {
			throw new Exception('No Data key in result');
		}
		if(isset($data['Data']['Results'])  && count($data['Data']['Results'])) {
			$skus = array();
			foreach($data['Data']['Results'] as $part) {
				$skus[] = $part['Sku'];
			}

			$oemData = $this->getOEMData($brandCode, $skus);

			$this->initSession();
			$result['customerId'] = (int)@$_SESSION['customer_base']['id'];
			$isWholesale = @$_SESSION['customer_base']['is_wholesale'];
			$customerCostPercent = @$_SESSION['customer_base']['cost_percent'];

			foreach($data['Data']['Results'] as $part) {
				$sku = $part['Sku'];

				if(isset($oemData[$sku]) && $oemData[$sku]['available']) { // if there is such record in OEM table
					$retailPrice = $oemData[$sku]['price']
						?	(float) $oemData[$sku]['price']
						:	(float) $part['MSRP'];

					$price = ($isWholesale && ($customerCostPercent > 0)) // if this is is a wholesale customer
						?	round($oemData[$sku]['cost'] * (100 + $customerCostPercent) / 100, 2)
						:	$retailPrice;

					$result['res'][] = array(
						'available' => 1,
						'id' => $part['PartId'],
						'sku' => trim($sku),
						'name' => trim(	$oemData[$sku]['part_name']
									?	$oemData[$sku]['part_name']
									:	$part['Description'] ),
						'msrp' => ($isWholesale ? $retailPrice : $oemData[$sku]['msrp']),
						'price' => $price,
						'hidePrice' => (int)$oemData[$sku]['hide_price'],
						'isSuperseded' => (bool)$part['IsSuperseded'], // If true, the original part data is prefixed by Org (bool)
						'nla' => (bool)$part['NLA'], // Stands for No Longer Available (bool)
						'hasModels' => (int)$part['HasModels'],
						'invW' => (int)$oemData[$sku]['inv_wh'],
//						'stockLabel' =>	$this->_config['stock_labels'][(int)($oemData[$sku]['inv_wh'] > 0)],
						'stockStatus' => (int)($oemData[$sku]['inv_wh'] > 0),
					);
				} else {
					$result['res'][] = array(
						'available' => 0,
						'id' => $part['PartId'],
						'sku' => trim($sku),
						'name' => $part['Description'],
					);
				}

			}
		}

		$result = array_merge_recursive($result, $this->_checkPaging($data));

		if(@self::$_params['debug']) { vd($result); }

		return $result;
	}



	protected function _processSearchModel($brandCode, $modelName, $sku, $paging = array(), $requestType = 'model')
	{
		$result = array(
//			'type' => 'model',
			'res' => array(),
		);

		$params = array(
			'brandCode' => $brandCode,
		);

		switch ($requestType) {
			case 'model':					// SearchModel
				$params['model'] = $modelName;
				break;

			case 'part_models':				// SearchPartModels
				$params['sku'] = $sku;
				break;

			case 'part_models_filtered':	// SearchPartModelsFiltered
				$params['sku'] = $sku;
				$params['modelName'] = $modelName;
				break;
		}

		$data = $this->request($this->_config['ari']['calls']['search'][$requestType], $params, $paging);

		if(@self::$_params['debug']) { vd($data); }
		if(!isset($data['Data'])) {
			throw new Exception('No Data key in result');
		}

		foreach($data['Data']['Results'] as $item) {
			$result['res'][] = array(
				'id' => $item['ModelId'],
				'name' => $item['ModelName'],
				'hash' => $this->hashName($item['ModelName']),
			);
		}

		$result = array_merge_recursive($result, $this->_checkPaging($data));

		return $result;
	}



	public function processSearch($params)
	{
		$brandCode = $params['brand'];
		$partNumber = $params['part'];
		$modelName = $params['model'];
		$searchType = @$params['type'];

		$paging = array();
		if (isset($params['page'])) {
			$paging['page'] = max(1, (int)$params['page']);
		}
		if (isset($params['pageSize'])) {
			$paging['pageSize'] = min(max($this->_config['ari']['search']['min_page_size'], (int)$params['pageSize']),
					$this->_config['ari']['search']['max_page_size']);
		} else {
			$paging['pageSize'] = $this->_config['ari']['search']['page_size'];
		}

		$result = array(
			'res' => array(),
			'type' => $searchType,
			'brand' => $brandCode,
			'model' => $modelName,
			'part' => $partNumber,
		);

		if(isset($params['partId'])) {
			$result['partId'] = $params['partId'];
		}

		switch ($searchType) {
			case 'M':
				$data = $this->_processSearchModel($brandCode, $modelName, '', $paging, 'model');
				break;

			case 'P':
				$data = $this->_processSearchPart($brandCode, $partNumber, '', $paging, 'parts');
				break;

			case 'PPWM':
				$data = $this->_processSearchPart($brandCode, $partNumber, $modelName, $paging, 'parts_within_models');
				break;

			case 'MPM':
				$data = $this->_processSearchModel($brandCode, '', $partNumber, $paging, 'part_models');
				break;

			case 'MPMF':
				$data = $this->_processSearchModel($brandCode, $modelName, $partNumber, $paging, 'part_models_filtered');
				break;

			default: // no line on horizon!
				$data = array();
		}

		$result = array_merge($result, $data);

		return $result;
	}



	public function processPartModels($params)
	{
		$result = array(
			'part' => $params['part'],
			'res' => array()
		);

		$data = $this->request($this->_config['ari']['calls']['search']['part_models'], $params);

		if(!isset($data['Data'])) {
			throw new Exception('No Data key in result');
		}

		foreach($data['Data']['Results'] as $item) {
			$result['res'][] = array(
				'id' => $item['ModelId'],
				'name' => $item['ModelName'],
				'queryName' => $item['ModelName'],
//				'hash' => $this->hashName($item['ModelName']),
			);
		}

		$this->convertValues('model', $params, $result['res']);

		foreach($result['res'] as &$item) {
			$item['hash'] = $this->hashName($item['name']);
		}
		unset($item);

		return $result;
	}



	public function processPartModelAssemblies($params)
	{
		$result = array(
			'modelId' => $params['modelId'],
			'part' => $params['part'],
			'res' => array()
		);

		if (isset($params['partId'])) {
			$result['partId'] = $params['partId'];
			unset($params['partId']);
		}

		$data = $this->request($this->_config['ari']['calls']['search']['part_model_assemblies'], $params);

		if(!isset($data['Data'])) {
			throw new Exception('No Data key in result');
		}

		foreach($data['Data'] as $item) {
			$result['res'][] = array(
				'id' => $item['Id'],
				'name' => $item['Name'],
//				'hash' => $this->hashName($item['Name']),
			);
		}

		$this->convertValues('assembly', $params, $result['res']);

		foreach($result['res'] as &$item) {
			$item['hash'] = $this->hashName($item['name']);
		}
		unset($item);

		return $result;
	}



	public function processAutocomplete($params)
	{
		$target = isset($params['target']) ? $params['target'] : 'part';
		$brandCode = $params['brand'];
		$value = @$params['value'];

		$result = array(
			'target' => $target,
			'brand' => $brandCode,
			'value' => $value,
			'res' => array(),
		);

		$data = $this->request($this->_config['ari']['calls']['autocomplete'][$target], array(
				'brand' => $brandCode,
				'search' => $value,
			), array(
				'numberOfResults' => $this->_config['ari']['autocomplete']['max_res_count']
			));

		if(@self::$_params['debug']) { vd($data); }
		if(!isset($data['Data'])) {
			throw new Exception('No Data key in result');
		}

		$paramName = ucfirst($target);

		foreach($data['Data'] as $item) {
			$result['res'][] = trim($item[$paramName]);
		}

		return $result;
	}

}

?>