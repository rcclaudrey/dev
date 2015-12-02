<?php

define('LF', chr(10));
define('CR', chr(13));
define('CRLF', CR . LF);

class Vikont_Data2data_Helper_Data extends Mage_Core_Helper_Abstract
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

		Vikont_Data2data_Helper_Log::log($message);
	}



	public static function type($message) {
		echo self::isCLIMode()
			? $message . "\n"
			: nl2br($message) . '<br/>';
	}



	public function getImportStorageLocation($mageRoot = null)
	{
		if(null === $mageRoot) {
			$mageRoot = dirname(dirname(getcwd()));
		}
		return ((false === strpos($mageRoot, ':\\')) ? '/' : '') . $mageRoot . '/var/data2data/'; // this must end with '/'
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



	/*
	 * Downloads the file
	 *
	 * @param string $filename The name of the file
	 * @param string $content The content to be downloaded; if NULL, will be read from $filename
	 * $param string $publicFilename Name for the file to be downloaded at
	 */
	public static function downloadFile($filename, $content = null, $publicFilename = null)
	{
		if($content === null) {
			if($handle = @fopen($filename, "r")) {
				$content = @fread($handle, filesize($filename));
				@fclose($handle);
			}
		}

		if(!$publicFilename) {
			$publicFilename = basename($filename);
		}

		$response = Mage::app()->getResponse();
		$response->setHeader('HTTP/1.1 200 OK','');
		$response->setHeader('Pragma', 'public', true);
		$response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
		$response->setHeader('Content-Disposition', 'attachment; filename="'.$publicFilename.'"');
		$response->setHeader('Last-Modified', date('r'));
		$response->setHeader('Accept-Ranges', 'bytes');
		$response->setHeader('Content-Length', strlen($content));
		$response->setHeader('Content-type', 'application/octet-stream');
		$response->setBody($content);
		$response->sendResponse();

		die;
	}



	public static function escapeCSVvalue($value)
	{
		if(false === $value || '' === $value || null === $value) {
			return '';
		} else if(is_int($value) || ctype_digit($value)) {
			return $value;
		}
		return '"'.addslashes(str_replace(CRLF, CR, $value)).'"';
	}

}