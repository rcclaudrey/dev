<?php

class Vikont_DB_Deployer
{
    // MySQLi link
    protected $_mysqli = null;


	public function initDb($host, $user, $pass, $dbname)
    {
        $this->_mysqli = new mysqli($host, $user, $pass, $dbname);

		if ($this->_mysqli->connect_errno) {
            return false;
        }
        return true;
    }


    public function dbQuery($sql, $errorMessage, $keyField = null)
    {
		$data = $this->_mysqli->query($sql);

		if(is_bool($data)) {
            return $data;
        }

		$result = array();
		while($row = $data->fetch_assoc()) {
			if($keyField) {
				$key = $row[$keyField];
				unset($row[$keyField]); // oh rly ???
				$result[$key] = $row;
			} else {
				$result[] = $row;
			}
		}

		return $result;
    }

}
