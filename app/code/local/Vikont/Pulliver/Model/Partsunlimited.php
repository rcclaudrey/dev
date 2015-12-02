<?php

/**
 *
 * Sample Class for Retrieving Price Files using the PHP Library
 *
 */
class Vikont_Pulliver_Model_Partsunlimited
{

	public function getZip()
	{
		$dealerCode = 'TEM026';
		$userName = 'DREW';
		$password = 'TEMECULA';
		$loginUrl = 'https://www.lemansnet.com/login';
		$pricingUrl = 'https://www.lemansnet.com/pricing/2013/pos';

		//Log In and Retrieve Token
		$myToken = $this->lemans_retrieve_token($dealerCode, $userName, $password, $loginUrl);

		if (!empty($myToken)){
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
			Vikont_Pulliver_Helper_Data::inform('User validation has failed and a request could not be made');
			return false;
			exit;
		}
	}



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
		$meta = stream_get_meta_data($stream);
//vd($meta);
//die;
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
			return '<h3 class="loginErrorMsg">Login Unsuccessful.  Please Try Again.</h3>';
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



	protected function lemans_process_http_response_header($http_response_header)
	{ //Search the Headers Array for the Token and Token Expires Values
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

?>