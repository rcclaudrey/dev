<?php

//ini_set('memory_limit', '1024M');
//ini_set('max_execution_time', '600');
//set_time_limit(0);

require_once 'Vic.php';

define('CR', chr(13));
define('BUFFER_SIZE', 1024 * 1024);

define('CLI', isset($argv));
define('NL', CLI ? "\n" : '<br/>');
$params = CLI ? $argv : $_GET;

$linesLimit = isset($params['limit']) ? $params['limit'] : 0;
$startAt = isset($params['start']) ? $params['start'] : 1;
$filename = isset($params['file']) ? $params['file'] : 'd:/Projects/Drew/upload/cd.txt';


// field names
$columns = array(
	array('name' => 'SkuId', 'db_length' => 50),
	array('name' => 'Parts Unlimited', 'db_length' => 50),
	array('name' => 'Tucker Rocky', 'db_length' => 50),
	array('name' => 'Wester Powersports', 'db_length' => 50),
	array('name' => 'Manufacture Part Number', 'db_length' => 50),
	array('name' => 'ProductId', 'db_length' => 50),
	array('name' => 'Category', 'db_length' => 50),
	array('name' => 'SubCategory', 'db_length' => 50),
	array('name' => 'AttributeTypes', 'db_length' => 1000),
	array('name' => 'ManufacturerName', 'db_length' => 100),
	array('name' => 'ImageUrl', 'db_length' => 100),
	array('name' => 'ProductDescription', 'db_length' => 10000),
	array('name' => 'PartName', 'db_length' => 500),
	array('name' => 'PartDescription', 'db_length' => 10000),
	array('name' => 'PartNumber', 'db_length' => 50),
	array('name' => 'DistributorName', 'db_length' => 50),
	array('name' => 'DistributorPartNumber', 'db_length' => 50),
	array('name' => 'Msrp', 'db_length' => 15),
);

//$columnLengths = array(50, 50, 50, 50, 50, 50, 50, 50, 1000, 100, 100, 10000, 500, 10000, 50, 50, 50, 15);

// ...and here the magic begins!
$startTime = time();

$handle = fopen($filename, 'rb');
if (false === $handle) {
	die('error opening file '.$filename);
}

$minLength = 100000;
$maxLength = 0;
$lineNumber = 0;
$linesProcessed = 0;
$prevBuffer = '';

while (!feof($handle)) {
	// adding previous part of the buffer //along with ending CR instead of EoF
	$buffer = $prevBuffer . fgets($handle, BUFFER_SIZE); // . (feof($handle) ? CR : '');
	$lastPos = strrpos($buffer, CR);
	if(false === $lastPos) {
		$lines = $buffer;
		$prevBuffer = '';
	} else {
		$lines = explode(CR, substr($buffer, 0, $lastPos));
		$prevBuffer = substr($buffer, $lastPos + 1);
	}

	foreach($lines as $line) {
		$lineNumber++;
		if($lineNumber < $startAt) {
			continue;
		}

		$linesProcessed++;
		if($linesLimit && $linesProcessed > $linesLimit) {
			break 2;
		}

		$minLength = min($minLength, strlen($line));
		$maxLength = max($maxLength, strlen($line));

		$fields = explode("\t", $line);

		$fieldCount = count($fields);
		if(count($fields) != 18) {
			echo 'count = '.$fieldCount.' at row '.$lineNumber.NL;
			break;
		}

	//	if($fieldCount < 18) {
	//		while(count($fields) < 18) {
	//			$fields[] = '';
	//		}
	//	} else if($fieldCount > 18) {
	//		while(count($fields) > 18) {
	//			unset($fields[count($fields) - 1]);
	//		}
	//	}

		foreach($fields as $fieldId => $field) {
			// checking for length exceeding
			$fieldLength = strlen($field);
			$columns[$fieldId]['maxLength'] = max(@$columns[$fieldId]['maxLength'], $fieldLength);
			if($fieldLength > $columns[$fieldId]['db_length']) {
				echo 'field '.$columns[$fieldId]['name'].' has length = '.$fieldLength.' at row '.$lineNumber.' '.$field.NL;
			}

			// checking for '#N/A'
//			if('#N/A' == $field) {
//				echo 'field '.$columns[$fieldId]['name'].' has #N/A at row '.$lineNumber.NL;
//			}

		}
	}
}

fclose($handle);

$timetaken = time() - $startTime;

// reporting
echo 'file name '.$filename.NL;
echo 'Started at line '.(int)$startAt.NL;
echo 'Lines processed '.$linesProcessed.NL;
echo 'Min line length = '.$minLength.NL;
echo 'Max line length = '.$maxLength.NL;
echo "time taken $timetaken s".NL;
echo NL.'Maximum column lengths:'.NL;
foreach($columns as $col) {
	echo $col['name'] . ' = ' . $col['maxLength'] . NL;
}

?>