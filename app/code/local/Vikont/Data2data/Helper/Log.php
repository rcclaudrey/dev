<?php

/*
 * Textual log class
 *
 * @author Victor aka Vikont707 <vikont707@gmail.com>
 */
class Vikont_Data2data_Helper_Log
{
//    const LOG_FILE_NAME = 'data2data.log';
    protected static $_logFileName = 'data2data.log';
    const LOG_DIR = '/log';

	const MYSQL_DATETIME_FORMAT = 'Y-m-d H:i:s';

    const TIME_MESSAGE_SEPARATOR        = ' ';

    const LOG_MESSAGE_PREFIX_SUCCESS    = 'SUCCESS: ';
    const LOG_MESSAGE_PREFIX_ERROR      = 'ERROR:   ';
    const LOG_MESSAGE_PREFIX_WARNING    = 'WARNING: ';
    const LOG_MESSAGE_PREFIX_DEBUG      = 'DEBUG:   ';


    /*
     * Logs message
     * @param string $message Message to log
     * @param string $prefix Message prefix (to make formatting tabulation in log file)
     */
    public static function log($message, $prefix='         ')
    {
        if(!self::_isLogEnabled()) return;

        $logDir = Mage::getModel('core/config')->getVarDir().self::LOG_DIR;
        $logPath = $logDir.DS.self::$_logFileName;

		if(!file_exists($logDir)) {
			mkdir($logDir, 0777, true);
		}

        if ($f = fopen($logPath, 'a+')) {
            $message = date(self::MYSQL_DATETIME_FORMAT, Mage::getModel('core/date')->timestamp()).self::TIME_MESSAGE_SEPARATOR.$prefix.$message;
            fwrite($f, $message . "\n");
            fclose($f);
        } else {
			Mage::log("Cannot open file '$logPath' for appending");
		}
    }


	public static function setLogFileName($fileName)
	{
		self::$_logFileName = $fileName;
	}



    /*
     * Logs message with SUCCESS prefix
     * @param string $message Message to log
     */
    public static function logSuccess($message)
    {
        self::log($message, self::LOG_MESSAGE_PREFIX_SUCCESS);
    }


    /*
     * Logs message with ERROR prefix
     * @param string $message Message to log
     */
    public static function logError($message)
    {
        self::log($message, self::LOG_MESSAGE_PREFIX_ERROR);
    }


    /*
     * Logs message with WARNING prefix
     * @param string $message Message to log
     */
    public static function logWarning($message)
    {
        self::log($message, self::LOG_MESSAGE_PREFIX_WARNING);
    }


    /*
     * Logs message with DEBUG prefix
     * @param string $message Message to log
     */
    public static function debug($message)
    {
        self::log($message, self::LOG_MESSAGE_PREFIX_DEBUG);
    }


    /*
     * Returns value of the "Log enabled" extension general configuration setting
     * @return string '0' or '1'
     */
    protected static function _isLogEnabled()
    {
        return Mage::getStoreConfigFlag('data2data/general/keeplog');
    }


}
