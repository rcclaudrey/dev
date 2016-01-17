<?php

class Vikont_Pulliver_Helper_PartsUnlimited extends Mage_Core_Helper_Abstract
{
	protected $_remoteHeaders = null;

	protected static $_columnNames = array(
		'Punctuated Part Number',
		'WI Availability',
		'NY Availability',
		'TX Availability',
		'CA Availability',
		'NV Availability',
		'NC Availability',
	);

	protected static $_columnPositions = array(0, 12, 13, 14, 15, 16, 17);



	public function getLocalFileName($fileName)
	{
		return Mage::helper('pulliver')->getImportStorageLocation() . 'partsunlimited/' . $fileName;
	}



	public function getRemoteFileName()
	{
		if(isset($this->_remoteHeaders['wrapper_data'])) {
			foreach($this->_remoteHeaders['wrapper_data'] as $row) {
				@list($key, $value) = explode(':', $row, 2);
				if(	'Content-disposition' == trim($key)) {
					@list($cd, $fileName) = explode(';filename=', $value, 2);
					return trim($fileName);
				}
			}
		}
		return 'remote.dump';
	}



	public function downloadFile()
	{
		Vikont_Pulliver_Helper_Data::type('Downloading data from Parts Unlimited API...');

		$startedAt = time();

		$fileContents = $this->getZipFileContents(
				trim(Mage::getStoreConfig('pulliver/parts_unlimited/dealer_code')),
				trim(Mage::getStoreConfig('pulliver/parts_unlimited/username')),
				trim(Mage::getStoreConfig('pulliver/parts_unlimited/password')),
				trim(Mage::getStoreConfig('pulliver/parts_unlimited/login_url')),
				trim(Mage::getStoreConfig('pulliver/parts_unlimited/pricing_url'))
			);

		$timeTaken = time() - $startedAt;

		if(!$fileContents) {
			Vikont_Pulliver_Helper_Data::throwException(sprintf('Error downloading from PU'));
		}

		$localFileName = $this->getLocalFileName($this->getRemoteFileName());

		$dirName = dirname($localFileName);
		if(!file_exists($dirName)) {
			mkdir($dirName, 0755, true);
		}

		$fileSize = file_put_contents($localFileName, $fileContents, FILE_BINARY);

		if($fileSize) {
			Vikont_Pulliver_Helper_Data::inform(sprintf(
					'Inventory successfully downloaded from PU API to %s, size=%dbyte(s), time=%ds',
					$localFileName, filesize($localFileName), $timeTaken));

			return $localFileName;
		} else {
			Vikont_Pulliver_Helper_Data::throwException(sprintf(
					'Error creating file %s downloaded from PU API, size=%dbyte(s)',
					$localFileName, filesize($localFileName)
			));

			return false;
		}
	}



	protected function _detectColumnPositions($cols)
	{
		foreach(self::$_columnNames as $colIndex => $colName) {
			if(false !== $colPosition = array_search($colName, $cols)) {
				self::$_columnPositions[$colIndex] = $colPosition;
			}
		}
	}



	public function parseFile($fileName)
	{
		if(!file_exists($fileName)) {
			Vikont_Pulliver_Helper_Data::throwException(sprintf('no such data file: %s', $fileName));
			return false;
		}

		$result = array();
		Vikont_Pulliver_Helper_Data::type("Parsing $fileName...");
		$fileHandle = fopen($fileName, 'r');
		$firstLine = true;

		while (false !== $values = fgetcsv($fileHandle)) {
			if($firstLine) {
				$this->_detectColumnPositions($values);
				$firstLine = false;
			}

			$items = array();

			foreach(self::$_columnPositions as $colIndex => $colPosition) {
				if(!$colIndex) {
					$items[] = $values[$colPosition]; // SKU
				} else {
					$items[] = ('+' == $values[$colPosition])
						?	10
						:	(int)$values[$colPosition];
				}
			}

			$result[] = $items;
		}

		fclose($fileHandle);

		return $result;
	}



	public function getZipFileContents($dealerCode, $userName, $password, $loginUrl, $pricingUrl)
	{
		//Log In and Retrieve Token
		$myToken = $this->lemans_retrieve_token($dealerCode, $userName, $password, $loginUrl);

		if ($myToken) {
			//Body Contents for Retrieving Zip File
			$zipRequestBody = <<<ZIPREQUESTBODY
<pricing>
	<whoForDealer>
		<dealerCode>$dealerCode</dealerCode>
	</whoForDealer>
	<rememberPreferences>1</rememberPreferences>
</pricing>
ZIPREQUESTBODY;

			//Make Request for Zip File
			return $this->lemans_request_pricing_files($pricingUrl, $zipRequestBody, $myToken, 'text/xml');
		} else {
			Vikont_Pulliver_Helper_Data::inform('PU error: User validation has failed and a request could not be made, message: ' . $myToken);
			return false;
		}
	}



// = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =
// below is the (almost) original code
// = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =



	protected function lemans_request_pricing_files($serviceUrl, $requestBody, $token, $contentType)
	{
		$params = array(
			'http' => array(
				'method' => 'POST',
				'content' => $requestBody
			));
		$requestHeaders = array(
			'Content-Type:'.$contentType,
			'Content-Length:'.strlen($requestBody),
			'charset:utf-8',
			'loginToken:'.$token,
			'Cache-Control:no-cache',
			'Pragma:no-cache',
			'Connection:keep-alive'
		);

		if ($requestHeaders !== null) {
			$params['http']['header'] = $requestHeaders;
		}
		$ctx = stream_context_create($params);
		$stream = fopen($serviceUrl, 'rb', false, $ctx);
		$this->_remoteHeaders = stream_get_meta_data($stream);
		$contents = stream_get_contents($stream);
		fclose($stream);

		return $contents;
	}



	protected function lemans_retrieve_token($dealerCode, $userName, $password, $loginURL)
	{
		$requestItems = array(
			'rememberMe' => 'on',
			'dealerCode' => $dealerCode,
			'dm' => 4,
			'userName' => $userName,
			'password' => $password
		);

		$requestString = $this->lemans_generate_query_parameter_string($requestItems);
		$tokenRequest = $this->lemans_do_token_request($loginURL . $requestString, null);

		if (!$tokenRequest) {
			Vikont_Pulliver_Helper_Data::throwException('PU: Login Unsuccessful');
		} else {
			return $tokenRequest;
		}
	}



	protected function lemans_generate_query_parameter_string($items = array())
	{
		if (!empty($items)) {
			$requestString = '';
			$cnt = 0;
			foreach ($items as $key => $item) {
				if (!empty($item)) {
					$requestString .= ($cnt == 0) ? '?' . $key . '=' . $item : '&' . $key . '=' . rawurlencode($item);
				}
				$cnt++;
			}
			return $requestString;
		} else {
			return false;
		}
	}



	//Search the Headers Array for the Token and Token Expires Values
	protected function lemans_process_http_response_header($http_response_header)
	{
		$tokenExpires = '';
		$token = '';
		foreach ($http_response_header as $key => $val) {
			$tokenSearchString = 'loginToken:';
			$tokenExpiresSearchString = 'loginTokenExpiry:';
			if (strpos($val, $tokenExpiresSearchString) !== false) {
				$tokenExpires = empty($tokenExpires) ? trim(str_replace($tokenExpiresSearchString, '', $val)) : $tokenExpires;
			} elseif (strpos($val, $tokenSearchString) !== false) {
				$token = empty($token) ? trim(str_replace($tokenSearchString, '', $val)) : $token;
			}
		}
		return array($token, $tokenExpires);
	}



	//Function borrowed from http://wezfurlong.org/blog/2006/nov/http-post-from-php-without-curl/
	protected function lemans_do_token_request($url, $data, $optional_headers = null)
	{
		$params = array(
			'http' => array(
				'method' => 'POST',
				'content' => $data
			));
		if ($optional_headers !== null) {
			$params['http']['header'] = $optional_headers;
		}
		$ctx = stream_context_create($params);
		$fp = @fopen($url, 'rb', false, $ctx);
		if (!$fp) {
			//Error Logging In, Return False
			//throw new Exception("Problem with " . $url, $php_errormsg);
			return false;
		}
		$response = @stream_get_contents($fp);
		if ($response === false) {
			//throw new Exception("Problem reading data from " . $url, $php_errormsg);
			return false;
		}
		//Get the Token and Expire Time
		list($token, $tokenExpires) = $this->lemans_process_http_response_header($http_response_header);

		return $token;
	}

}