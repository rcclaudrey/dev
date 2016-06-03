<?php

/*
 * Merges arrays recursively, overwriting left array's values with right one's if exist, and adding if not
 */
function mergeArray($a1, $a2)
{
	foreach($a2 as $key => $value) {
		if(!isset($a1[$key]) || !is_array($a1[$key])) {
			$a1[$key] = $value;
		} else {
			$a1[$key] = mergeArray($a1[$key], $value);
		}
	}
	return $a1;
}



function arraySearchByField($arr, $searchBy, $searchValue, $searchFor = null)
{
	foreach($arr as $value) {
		if(isset($value[$searchBy]) && ($value[$searchBy] == $searchValue)) {
			if(null === $searchFor) {
				return $value;
			} else if (isset($value[$searchFor])) {
				return $value[$searchFor];
			} else {
				return null;
			}
		}
	}
	return null;
}



/*
 * @see http://php.net/manual/en/function.session-decode.php#108037
 */
function decode_session($session_data)
{
	$method = ini_get("session.serialize_handler");
	switch ($method) {
		case "php":
			$return_data = array();
			$offset = 0;
			while ($offset < strlen($session_data)) {
				if (!strstr(substr($session_data, $offset), "|")) {
					throw new Exception("invalid data, remaining: " . substr($session_data, $offset));
				}
				$pos = strpos($session_data, "|", $offset);
				$num = $pos - $offset;
				$varname = substr($session_data, $offset, $num);
				$offset += $num + 1;
				$data = unserialize(substr($session_data, $offset));
				$return_data[$varname] = $data;
				$offset += strlen(serialize($data));
			}
			return $return_data;
			break;

		case "php_binary":
			$return_data = array();
			$offset = 0;
			while ($offset < strlen($session_data)) {
				$num = ord($session_data[$offset]);
				$offset += 1;
				$varname = substr($session_data, $offset, $num);
				$offset += $num;
				$data = unserialize(substr($session_data, $offset));
				$return_data[$varname] = $data;
				$offset += strlen(serialize($data));
			}
			return $return_data;
			break;

		default:
			throw new Exception("Unsupported session.serialize_handler: $method. Supported: php, php_binary");
	}
}