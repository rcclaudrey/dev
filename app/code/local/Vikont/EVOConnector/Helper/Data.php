<?php

class Vikont_EVOConnector_Helper_Data extends Mage_Core_Helper_Abstract
{
	const ORDER_EVO_STATUS_FIELD = 'evo_status';

	const ORDER_EVO_STATUS_NEW = 0;
	const ORDER_EVO_STATUS_READY = 1;
	const ORDER_EVO_STATUS_SENT = 2;
	const ORDER_EVO_STATUS_APPROVED = 3;



	/*
     * Returns whether module output is disabled from Advanced->Disable module output in Admin Config section
	 *
     * @return bool
     */
    public static function isModuleOutputDisabled()
    {
        return Mage::getStoreConfigFlag('advanced/modules_disable_output/Vikont_EVOConnector');
    }


	public static function isModuleAllowed()
	{
		return Mage::getStoreConfigFlag('evoc/general/enabled') && !self::isModuleOutputDisabled();
	}


    /*
     * AJAX or XML response
     */
    public static function sendResponse($data, $type = 'xml')
    {
		$responseText = '';

		switch($type) {
			case 'xml':
				header('content-type: application/xml');
				$responseText = is_array($data) ? self::array2xml($data) : $data;
				break;

			case 'json':
				header('content-type: application/json');
				Zend_Json::$useBuiltinEncoderDecoder = true;
		        $responseText = Zend_Json::encode($obj);
				break;

			default:
				header('content-type: text/plain');
				$responseText = $data;
		}

		echo $responseText;
		die();
    }


	/*
	 * Converts array data to XML text representation
	 *
	 * @param array @data Array to convert
	 */
	public static function array2xml($data)
	{
		$result = '';

		foreach($data as $key => $value) {
			switch (gettype($value)) {
				case 'array':
					$text = chr(13).self::array2xml($value);
					break;
				case 'object':
					$text = 'object of '.get_class($value);
					break;
				case 'resource':
					$text = 'resource';
					break;
				case NULL:
					$text = '';
					break;
				case 'boolean':
					$text = ''.(bool) $value;
					break;
				case 'integer':
					$text = ''.(int) $value;
					break;
				case 'double':
					$text = ''.(double) $value;
					break;
				case 'string':
				default:
					$text = htmlspecialchars((string)$value);
			}
			$result .= '<'.htmlspecialchars($key).'>'.$text.'</'.htmlspecialchars($key).'>'.chr(13);
		}

		return $result;
	}


	public static function parseXML($xmlText)
	{
		libxml_use_internal_errors(true);
		$xml = simplexml_load_string($xmlText);

		if (!$xml) {
			Vikont_EVOConnector_Model_Log::log('XML parsing errors: ');

			foreach(libxml_get_errors() as $error) {
				Vikont_EVOConnector_Model_Log::log("\t" . trim($error->message, " \n\l\t"));
			}
			Vikont_EVOConnector_Model_Log::log('XML text:' . ($xmlText ? "\n" . $xmlText : ' empty'));
		}
		return $xml;
	}



	/*
	 * Formats date with Zend_Date format specified
	 */
	public static function getDateFormatted($date, $format = 'YYYY-MM-dd HH:mm:ss')
	{
		$localDate = Mage::app()->getLocale()->date(strtotime($date), null, null);
		return $localDate->toString($format);
	}



	public static function getDistributors()
	{
		return array(
			'TR' => array('d_trocky', 'Tucker Rocky'),
			'WP' => array('d_wpower', 'Western Power Sports'),
			'PU' => array('d_punlim', 'Parts Unlimited'),
			'PO' => array('d_polaris', 'Polaris'),
			'CA' => array('d_canam', 'Can-Am'),
			'FX' => array('d_fox', 'Fox Racing'),
			'HH' => array('d_hhouse', 'Helmet House'),
			'HO' => array('d_honda', 'Honda'),
			'KA' => array('d_kawasaki', 'Kawasaki'),
			'SD' => array('d_seadoo', 'SeaDoo'),
			'SU' => array('d_suzuki', 'Suzuki'),
			'YA' => array('d_yamaha', 'Yamaha'),
			'TL' => array('d_troylee', 'Troy Lee'),
		);
	}



	public static function combineArray($data)
	{
		$result = '';
		foreach($data as $key => $value) {
			if($value) {
				$result .= $key . ': ' . $value . ', ';
			}
		}
		return trim($result, ' ,');
	}

}