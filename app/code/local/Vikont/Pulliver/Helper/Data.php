<?php

class Vikont_Pulliver_Helper_Data extends Mage_Core_Helper_Abstract
{
	protected static $_silentException = true;
	protected static $_CLIMode = null;


	public static function setSilentExceptions($type = true)
	{
		self::$_silentException = $type;
	}



	public static function throwException($message)
	{
		if (self::$_silentException) {
			self::inform($message);
			return false;
		} else {
			throw new Exception($message);
		}
	}



	public static function isCLIMode()
	{
		if(null === self::$_CLIMode) {
			self::$_CLIMode = (php_sapi_name() == 'cli');
		}
		return self::$_CLIMode;
	}



	public static function inform($message) {
		echo self::isCLIMode()
			? $message . "\n"
			: nl2br($message) . '<br/>';

		Vikont_Pulliver_Model_Log::log($message);
	}



	public static function type($message) {
		echo self::isCLIMode()
			? $message . "\n"
			: nl2br($message) . '<br/>';
	}



	public function getImportStorageLocation()
	{
		$MageRoot = dirname(dirname(getcwd()));

		return ((false === strpos($MageRoot, ':\\')) ? '/' : '') . trim(
				$MageRoot . '/' . trim(Mage::getStoreConfig('pulliver/general/import_location'), ' /'),
			'/') . '/';
	}



	public function pullData($url, $user, $password, $contentType = 'text/json')
	{
		$ch = curl_init();
		curl_setopt_array($ch, array(
			CURLOPT_URL => $url,
			CURLOPT_VERBOSE => 0,
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
			CURLOPT_USERPWD => $user . ':' . $password,
			CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_SSL_VERIFYHOST => 2,
			CURLOPT_HTTPHEADER => array('Accept: ' . $contentType),
		));
		$response = curl_exec($ch);
		$errNo = curl_errno($ch);

		if($errNo) {
			$curlError = curl_error($ch);
		} else {
			$curlError = '';
		}

		curl_close($ch);

		if($errNo) {
			Mage::throwException(sprintf('CURL error #%d: %s', $errNo, $curlError));
		}

		return $response;
	}



	public function openFile($fileName)
	{
		if(file_exists($fileName)) {
			@unlink($fileName);
		} else {
			$dirName = dirname($fileName);
			if(!file_exists($dirName)) {
				mkdir($dirName, 0700, true);
			}
		}

		$f = fopen($fileName, 'wb');
		if (!$f) {
			self::throwException("Cannot open file $fileName for writing");
			return false;
		}

		return $f;
	}



	public function saveFile($fileName, $content)
	{
		$fileHandle = $this->openFile($fileName);
		fwrite($fileHandle, $content);
		fclose($fileHandle);
		return $fileName;
	}



	public static function getCommandLineParams($args)
	{
		$result = new Varien_Object();

		foreach($args as $argument) {
			@list($key, $value) = explode('=', $argument, 2);
			if(NULL === $value) {
				$value = true;
			}
			$result->setData($key, $value);
		}

		return $result;
	}



	public static function unZip($source, $dest)
	{
		$zip = new ZipArchive;
		$res = $zip->open($source);

		if($res === TRUE) {
			$res = $zip->extractTo($dest);
			$zip->close();
			return $res;
		}

		return $res;
	}



	public static function getDirectoryListing($directory)
	{
		$res = array_flip(scandir($directory));
		unset($res['.']);
		unset($res['..']);
		return $res;
	}


}