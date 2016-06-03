<?php

define('CR', "\n");


class Deployer
{
	const MYSQL_DATETIME_FORMAT = 'Y-m-d H:i:s';

    // MySQLi link
    protected $_link = null;

    // Log file name
    protected $_logFileName = 'deployer.log';

    // Space between the date and message in the log
    const LOG_SPACE = '    ';

	// whether the script was ran from console
	protected static $_isCLIMode = false;


    // Splits query string to single queries
    public static function splitQuery($s)
    {
        $parts = explode(';', $s);

        $glue = null;

        foreach($parts as $key => $part) {
            if(!$part) {
                unset($parts[$key]);
                continue;
            }
            if(null !== $glue) {
                $parts[$key] = $parts[$glue] . $parts[$key];
                unset($parts[$glue]);
                $glue = null;
            }
            $s = $parts[$key];
            if(self::checkQuotes($s))
                $glue = $key;
        }

        return $parts;
    }


    // Checks whether gluing needed for splitQuery()
    protected static function checkQuotes($s)
    {
        $s = str_replace('\\\'', '', $s);

        $count = 0;
        for($i=0; $i<strlen($s); $i++)
            if($s[$i] == '\'') $count++;

        return $count % 2;
    }


    public function initDb($host, $user, $pass, $dbname)
    {
        // Connecting to the database
        $this->_link = mysqli_connect($host, $user, $pass);
        if (mysqli_connect_errno()) {
            $this->logMessage('MySQL connect failed: '.mysqli_connect_error());
            return false;
        }

        // Creating the database
        if(!$this->dbQuery('CREATE DATABASE IF NOT EXISTS '.$dbname.' CHARACTER SET utf8', 'Error creating database!'))
            return false;

        // Using the database
        if(!$this->dbQuery('USE '.$dbname, 'Error using database!')) {
            return false;
        }

        return true;
    }


    // Performs Multiple DB query
    public function dbMultiQuery($sql, $errorMessage)
    {
        // Splitting the file to single queries
        $queries = self::splitQuery($sql);

        // Running queries one by one
        foreach($queries as $query) {
            if(!$this->dbQuery($query, $errorMessage)) {
//				$this->logMessage($errorMessage.CR.substr($query, 0, 1000).CR.mysqli_errno($this->_link).' '.mysqli_error($this->_link));
//                return false;
            }
        }
        return true;
    }


    // Performs single query
    public function dbQuery($sql, $errorMessage)
    {
        // multi_query version
        // if(!mysqli_multi_query($this->_link, $sql)) {
            // return $errorMessage.CR.$sql.CR.mysqli_errno($this->_link).' '.mysqli_error($this->_link);
        // } else
            // do {
                // if ($result = mysqli_store_result($this->_link)) {
                    // mysqli_free_result($result);
                // }
            // } while (mysqli_next_result($this->_link));

        if(!$result = mysqli_query($this->_link, $sql)) {
            self::logMessage($errorMessage.CR.substr($sql, 0, 10000).CR.mysqli_errno($this->_link).' '.mysqli_error($this->_link));
            return false;
        } else {
            if(!is_bool($result))
                mysqli_free_result($result);

            return true;
        }
    }


    // Escapes a scring to be inserted to SQL query
    public function escape($s)
    {
        return mysqli_real_escape_string($this->_link, $s);
    }


    // Returns time for each log record
    protected static function getLogTime()
    {
        return date(self::MYSQL_DATETIME_FORMAT);
    }


    // Initializes the deployment log
    public function initLog($fileName)
    {
		self::$_isCLIMode = (php_sapi_name() == 'cli');
		date_default_timezone_set('UTC');
        $this->_logFileName = $fileName;
		$this->logMessage('Deployment started');
    }


    // Logs message to the file specified
    public function logMessage($message)
    {
		$message = self::getLogTime().self::LOG_SPACE.$message.CR;

		file_put_contents($this->_logFileName, $message, FILE_APPEND);

		if(!self::$_isCLIMode) {
			$message = nl2br(htmlspecialchars($message));
		}

		echo $message;

        return $message;
    }

}
