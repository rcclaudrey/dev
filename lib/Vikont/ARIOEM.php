<?php

define('ARI_APP_KEY', '9oiOWqDlvNLyUXT4Qtun');
define('ARI_REFERER_URL', 'http://www.tmsparts.com/');
define('ARI_LANGUAGE_CODE', 'en-US');
define('ARI_STREAM_ENDPOINT', 'https://partstream.arinet.com');
define('ARI_URL_PARTS_MANUFACTURER', '/Parts/Script');
define('ARI_URL_PARTS_GET_ASSEMBLY', '/Parts/GetAssembly');
define('ARI_URL_PARTS_GET_AUTOCOMPLETE', '/Parts/GetAutocomplete');
define('ARI_URL_PARTS_GET_DETAIL', '/Parts/GetDetails');
define('ARI_URL_PARTS_SEARCH_INDEX', '/Search');

define('ARI_API_RETRY_MAX_COUNT', 10);
define('ARI_API_RETRY_TIME', 100);

//usage:
// http://1901.loc/arioem/ same as
// http://1901.loc/arioem/index.php same as
// http://1901.loc/arioem/index.php?action=brand brand list
//
// http://1901.loc/arioem/index.php?action=year&brand=YAM&hash={secret_key} for assembly data
// http://1901.loc/arioem/index.php?action=model&brand=YAM&hash={secret_key} for assembly data
// http://1901.loc/arioem/index.php?action=part&brand=YAM&hash={secret_key} for assembly data
//
// http://1901.loc/arioem/index.php?action=search&key=part&search=am [ &brand=KUS ] by part number [ with brand ]
// http://1901.loc/arioem/index.php?action=search&key=model&search=am [ &brand=KUS ] by model name [ with brand ]

class Vikont_ARIOEM
{

	public function dispatch()
	{
		$params = $_GET;
		unset($params['_']); // the jQuery parameter preventing caching
		$action = isset($params['action']) ? $params['action'] : 'brand';
		$result = '';

		switch($action) {
			case 'brand':
				$result = array(
					'res' => $this->getBrands()
				);
				break;

			case 'search':
				$searchParams = array();
				$result = $params; // keeping extra parameters passed in the URL
				unset($result['action']);

				foreach(array('brand', 'search', 'key') as $paramName) {
					if(isset($params[$paramName])) {
						$searchParams[$paramName] = $params[$paramName];
						unset($result[$paramName]);
					}
				}
				$result['res'] = $this->getSearchData($searchParams);
				break;

			case 'content':
				$content = $this->request($this->composeURL(ARI_URL_PARTS_MANUFACTURER));
				$content = trim($content, 'document.write(\');');
				$result = array(
					'res' => $this->decodeHTMLResponse($content)
				);
				break;

			case 'year':
			case 'model':
			case 'part':
				$result = array(
					'res' => $this->getAssemblyData(array(
						'arib' => @$params['brand'],
						'aria' => @$params['hash'],
					), $action)
				);
				break;
		}

		return $result;
	}



	public function request($url, $params = array())
	{
		$ch = curl_init();

		if(count($params)) {
			$url .= ((false === strpos($url, '?')) ? '?' : '&' ) . http_build_query($params);
		}

		curl_setopt_array($ch, array(
			CURLOPT_URL => $url,
			CURLOPT_VERBOSE => 0,
			CURLOPT_RETURNTRANSFER => 1,
//			CURLOPT_SSLVERSION => 3,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_SSL_VERIFYHOST => 2,
		));

		$error = false;

		for($retry = 1; $retry < ARI_API_RETRY_MAX_COUNT; $retry++) {
			if($error) {
				usleep(ARI_API_RETRY_TIME);
			}

			$response = curl_exec($ch);

			if(false !== $response) {
				break;
			} else {
				$error = curl_error($ch);
			}
		}

		curl_close($ch);

		if(false !== $error) {
			throw new Exception('CURL error: ' . $error);
		}

		return $response;
	}



	public function composeURL($action)
	{
		return ARI_STREAM_ENDPOINT . $action . '?' . http_build_query(array(
				'arik' => ARI_APP_KEY,
				'aril' => ARI_LANGUAGE_CODE,
				'ariv' => ARI_REFERER_URL,
			));
	}



	public function decodeHTMLResponse($text)
	{
		$translationTable = array(
			'\u003c' => '<',
			'\u003e' => '>',
			'\u0027' => '\'',
			'\u0026' => '&',
			'\r' => chr(13),
			'\n' => chr(10),
		);

		foreach($translationTable as $search => $replace) {
			$text = str_replace($search, $replace, $text);
		}

		return stripslashes($text);
	}



	public function getBrands()
	{
		$content = $this->request($this->composeURL(ARI_URL_PARTS_MANUFACTURER));
		$content = trim($content, 'document.write(\');');
		$content = $this->decodeHTMLResponse($content);

		$dom = new DOMDocument;
		$dom->loadHTML($content);

		$select = $dom->getElementById('ari_brands');
		if(!$select) {
			throw new Exception('No #ari_brands element found in the response');
		}

		$result = array();

		foreach($select->childNodes as $itemIndex => $node) {
			if($itemIndex) {
//				$result[$node->attributes->getNamedItem('value')->value] = $node->textContent;
				$result[] = array(
					$node->attributes->getNamedItem('value')->value,
					$node->textContent
				);
			}
		}

		return $result;
	}



	public function getAssemblyData($parameters, $action = '')
	{
		if('year' == $action && 'HONPE' == $parameters['arib']) {
			$result = array();
			date_default_timezone_set('UTC');
			for($year = (int)date('Y'); $year >= 1975; $year--) {
				$result[] = array($parameters['aria'], $year, '');
			}
			return $result;
		}

		$content = $this->request($this->composeURL(ARI_URL_PARTS_GET_ASSEMBLY), $parameters);
		if(@$_GET['debug']) vd($content); // let it be here

		$data = json_decode($content, true);
		if(@$_GET['debug']) vd($data); // let it be here

		if(null === $data) {
			throw new Exception(sprintf('Cannot parse a response from %s%s with parameters=%s response=%s',
					ARI_STREAM_ENDPOINT, ARI_URL_PARTS_GET_ASSEMBLY, print_r($parameters, true), $content));
		}

		$data = (array) $data;
		$result = array();

		foreach(@$data['model']['json'] as $item) {
			$result[] = array(
				@$item['attr']['aria'],
				@$item['data'],
				@$item['attr']['slug']
			);
		}

		if('year' == $action) {
			if(1 == count($result)) {
				if(!$result[0][1]) {
					$result[0][1] = 'All';
				}
			} else {
				foreach($result as &$item) {
					$yearValue = preg_replace('/[\D]*/i', '', $item[1]);
					$item[1] = $yearValue ? $yearValue : $item[1];
				}
			}
		} elseif('model' == $action) {
			if('BRP' == $parameters['arib'] || 'BRP_SEA' == $parameters['arib']) {
				$data = array();

				foreach($result as $item) {
					$models = $this->getAssemblyData(array(
							'arib' => $parameters['arib'],
							'aria' => $item[0],
						));
					$data = array_merge($data, $models);
				}
				$result = $data;
			}

			$result = $this->convertNames($parameters['arib'], $result);
		}

		return $result;
	}



	public function convertNames($brand, $data)
	{
		switch($brand) {
			case 'POL':
			case 'KUS':
			case 'VIC':
				foreach($data as &$item) {
					$firstSpacePos = strpos($item[1], ' ');
					$openBracePos = strpos($item[1], '(');
					if(false !== $openBracePos && false !== $openBracePos) {
						$item[1] = substr($item[1], $firstSpacePos + 1, $openBracePos - $firstSpacePos) . 'Model - ' . substr($item[1], 0, $firstSpacePos) . ')';
					}
				}
				break;

			case 'HOM':
				foreach($data as &$item) {
					$openBracePos = strpos($item[1], '(');
					if(false !== $openBracePos) {
						$item[1] = substr($item[1], 0, $openBracePos);
					}
				}
				break;

			case 'YAM':
				foreach($data as &$item) {
					$lastDashPos = strrpos($item[1], '-');
					$year = substr($item[1], $lastDashPos + 1);
					if(false !== $lastDashPos && $year && is_numeric($year)) {
						$item[1] = rtrim(substr($item[1], 0, $lastDashPos));
					}
				}
				break;

			case 'SUZ':
				foreach($data as &$item) {
					$lastBracketPos = strrpos($item[1], '(');
					if(false !== $lastBracketPos && is_numeric(substr($item[1], $lastBracketPos+1, 4))) {
						$item[1] = rtrim(substr($item[1], 0, $lastBracketPos));
					}
				}
				break;

			case 'BRP':
			case 'BRP_SEA':
				foreach($data as &$item) {
					$item[1] = rtrim(rtrim($item[1], '0123456789'), ' ,');
				}
				break;

			case 'HONPE':
				foreach($data as &$item) {
					$vinPos = strpos($item[1], 'VIN#');
					if($vinPos) {
						$item[1] = trim(substr($item[1], 0, $vinPos), ' ,');
					}
				}
				break;
		}
		return $data;
	}



	public function getSearchData($parameters)
	{
		$content = $this->request($this->composeURL(ARI_URL_PARTS_GET_AUTOCOMPLETE), $parameters);
		$data = json_decode($content, true);

		if(null === $data) {
			throw new Exception(sprintf('Cannot parse a response from %s%s with parameters=%s response=%s',
					ARI_STREAM_ENDPOINT, ARI_URL_PARTS_GET_AUTOCOMPLETE, print_r($parameters, true), $content));
		}

		$data = (array) $data;
		$result = array();

		foreach(@$data['model'] as $item) {
			$result[] = array(
				@$item['Brand'],
				@$item['Data'],
			);
		}

		return $result;
	}


}

?>