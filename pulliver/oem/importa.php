<?php

header('Pragma: no-cacne');
header('Cache-Control: no-cache');

ini_set('memory_limit', '1024M');
ini_set('max_execution_time', '600');
set_time_limit(0);

define('MAX_QUERY_ROWS', 100);
define('MAX_DELETE_RECORDS', 1000);
define('DEPLOY_LOG_FILE', 'oem-import.log');

$currentDir = dirname(__FILE__);
$rootDir = dirname(dirname($currentDir)) . '/';

require $rootDir . 'Vic.php';

$debugSQLDump = (isset($_GET['debug_sqldump']) && $_GET['debug_sqldump']);
$debugImitateQuery = (isset($_GET['debug_imitateQuery']) && $_GET['debug_imitateQuery']);
$debugDumpSrcData = (isset($_GET['debug_dumpSrcData']) && $_GET['debug_dumpSrcData']);
$debugDumpColAssignment = (isset($_GET['debug_cols']) && $_GET['debug_cols']);

require_once $currentDir . '/functions.php';
require_once $currentDir . '/Deployer.php';
$deployer = new Deployer();
$deployer->initLog($rootDir . 'var/log/' . DEPLOY_LOG_FILE);

require_once $currentDir . '/creds.php';

if($deployer->initDb($dbhost, $dbUser, $dbPass, $dbName)) {
    $deployer->logMessage("DB init successful\n");
} else {
    die('DB init not successful');
}

$params = $_GET;

$fileName = $rootDir . ltrim($params['file'], ' /');

$rowsInsertedTotal = 0;
$rowsTotal = 0;
$rowsProcessed = 0;
$rowsProcessedTotal = 0;
$dataString = '';

$deployer->logMessage("Processing $fileName");

$handle = fopen($fileName, "rb");

if(false === $handle) {
	$deployer->logMessage("Could not open $fileName for reading");
	die;
}

$col1stLineNames = fgetcsv($handle);
if (!is_array($col1stLineNames)) {
	$deployer->logMessage('ERROR: No column names line in the file');
	die;
}
$colNames = array_map('strtolower', $col1stLineNames);

// replacing similar column names with the proper ones ready for SQL
$colNameConversion = array(
	'supplier_code' => array('brand'),
	'part_number' => array('sku', 'partnumber'),
	'part_name' => array('name'),
	'available' => array('avail', 'enabled'),
	'cost' => array(),
	'msrp' => array('retail', 'retail price'),
	'price' => array('sale_price'),
	'hide_price' => array(),
	'inv_local' => array('local'),
	'inv_wh' => array('wh', 'wholesale'),
	'dim_length' => array('length'),
	'dim_width' => array('width'),
	'dim_height' => array('height'),
	'weight' => array(),
	'oversized' => array(),
	'uom' => array(),
	'image_url' => array('image'),
	'delete' => array('remove'),
);

$colPositions = array();

foreach($colNameConversion as $colSQLName => $colPossibleNames) {
	$colPossibleNames[] = $colSQLName;

	foreach($colPossibleNames as $colPossibleName) {
		$colIndex = array_search($colPossibleName, $colNames);
		if (false !== $colIndex) {
			$colNames[$colIndex] = $colSQLName;
			break;
		}
	}

	$colIndex = array_search($colSQLName, $colNames);
	if (false !== $colIndex) {
		$colPositions[$colSQLName] = $colIndex;
	}
}

if (!isset($colPositions['supplier_code']) || !isset($colPositions['part_number'])) {
	$deployer->logMessage('ERROR: No supplier code and part number columns in the file');
	die;
}

$deleteColIndex = false;
if (isset($colPositions['delete'])) {
	$deleteColIndex = $colPositions['delete'];
	unset($colPositions['delete']);
}

$yesNoColIndexes = array();
if (isset($colPositions['available'])) $yesNoColIndexes[] = $colPositions['available'];
if (isset($colPositions['hide_price'])) $yesNoColIndexes[] = $colPositions['hide_price'];
if (isset($colPositions['oversized'])) $yesNoColIndexes[] = $colPositions['oversized'];

$priceColIndexes = array();
if (isset($colPositions['cost'])) $priceColIndexes[] = $colPositions['cost'];
if (isset($colPositions['msrp'])) $priceColIndexes[] = $colPositions['msrp'];
if (isset($colPositions['price'])) $priceColIndexes[] = $colPositions['price'];

$nonEmptyColIndexes = array();
if (isset($colPositions['inv_local'])) $nonEmptyColIndexes[] = $colPositions['inv_local'];
if (isset($colPositions['inv_wh'])) $nonEmptyColIndexes[] = $colPositions['inv_wh'];

if ($debugDumpColAssignment) {
	vd($colPositions);
	vd('delete column index = ' . $deleteColIndex);
	echo 'Yes/No column indexes:';
	vd($yesNoColIndexes);
	echo 'Price column indexes:';
	vd($priceColIndexes);
	echo 'Non-empty column indexes:';
	vd($nonEmptyColIndexes);
}

$updatedColumns = $colPositions;
unset($updatedColumns['part_number']);
unset($updatedColumns['supplier_code']);

$colAssignment = array();
foreach($updatedColumns as $colName => $colIndex) {
	$colAssignment[] = $colName . '=VALUES(' . $colName . ')';
}

$sqlUpdateTemplate = 'INSERT INTO `oem_cost`(%COLUMN_NAMES%) VALUES %VALUES% ON DUPLICATE KEY UPDATE %COLUMN_ASSIGNMENT%';
$sqlUpdateTemplate = str_replace('%COLUMN_NAMES%', implode(', ', array_keys($colPositions)), $sqlUpdateTemplate);
$sqlUpdateTemplate = str_replace('%COLUMN_ASSIGNMENT%', implode(', ', $colAssignment), $sqlUpdateTemplate);

$deleteRecords = array();

while(true) {
	$rowData = fgetcsv($handle);

	if(false === $rowData || $rowsProcessed >= MAX_QUERY_ROWS) {
		if($dataString) {
			$sql = str_replace('%VALUES%', rtrim($dataString, ','), $sqlUpdateTemplate);
//				'INSERT INTO `oem_cost`('.implode(',', $columns).') VALUES ' . rtrim($dataString, ',')
//				. ' ON DUPLICATE KEY UPDATE cost=VALUES(cost), part_name=VALUES(part_name)';

			if($debugSQLDump) sql($sql);

			$result = $debugImitateQuery || $deployer->dbQuery($sql, 'Error running query!');

			if(!$result) {
				$deployer->logMessage($result);
				sql($sql);
				break;
			}
			$dataString = '';
			$rowsProcessedTotal += $rowsProcessed;
			$rowsInsertedTotal += $rowsProcessed;
			$rowsProcessed = 0;
		}

		if(false === $rowData) {
			$deployer->logMessage("\nImporting $fileName completed, total rows processed = $rowsTotal, total rows imported = $rowsProcessedTotal");
			break;
		}
	}

	$rowsTotal++;

	if($debugDumpSrcData) vd($rowData);

	if (count($colNames) > count($rowData)) {
		$deployer->logMessage('ERROR: The row has less values than the header: ' . implode(',', $rowData));
		continue;
	}
	if (!isset($rowData[$colPositions['supplier_code']]) || !$rowData[$colPositions['supplier_code']]) {
		$deployer->logMessage('ERROR: Supplier code not specified: ' . implode(',', $rowData));
		continue;
	}
	if (!isset($rowData[$colPositions['part_number']]) || !$rowData[$colPositions['part_number']]) {
		$deployer->logMessage('ERROR: Part # not specified: ' . implode(',', $rowData));
		continue;
	}

	if ((false !== $deleteColIndex) && normalizeYesNo($rowData[$deleteColIndex])) { // remove it!
		$brandCode = $rowData[$colPositions['supplier_code']];
		if (!isset($deleteRecords[$brandCode])) {
			$deleteRecords[$brandCode] = array();
		}
		$deleteRecords[$brandCode][] = addslashes($rowData[$colPositions['part_number']]);
	} else {
		$sqlData = array();

		foreach($colPositions as $colName => $colIndex) {
			$value = trim($rowData[$colIndex]);

			if (in_array($colIndex, $yesNoColIndexes)) {
				$colValue = (int)normalizeYesNo($value);
			} else if (in_array($colIndex, $priceColIndexes)) {
				$colValue = round(str_replace(array(' ', ','), '', $value), 2);
			} else if (in_array($colIndex, $nonEmptyColIndexes)) {
				$colValue = $rowData[$colIndex]
					?	addslashes($value)
					:	'0';
			} else {
				$colValue = addslashes($value);
			}

			$sqlData[] = $colValue;
		}

		$dataString .= '(\'' . implode('\',\'', $sqlData) . '\'),';
	}

	$rowsProcessed++;
}
fclose($handle);


// now let's remove the records having "delete" flag
$recordsToRemove = 0;

if (count($deleteRecords)) {
	$deployer->logMessage('Removing the marked records');

	$sqlDeleteTemplate = 'DELETE IGNORE FROM `oem_cost` WHERE supplier_code="%BRAND%" AND part_number IN (%PART_NUMBERS%)';

	foreach($deleteRecords as $brandCode => $partNumbers) {
		if (!count($partNumbers)) continue;

		$recordsToRemove += count($partNumbers);

		$deployer->logMessage(sprintf('Removing %s %d records', $brandCode, count($partNumbers)));

		$sqlTemplate = str_replace('%BRAND%', $brandCode, $sqlDeleteTemplate);
		$parts = array_chunk($partNumbers, MAX_DELETE_RECORDS);

		foreach($parts as $partList) {
			$sql = str_replace('%PART_NUMBERS%', '"' . implode('","', $partList) . '"', $sqlTemplate);

			if($debugSQLDump) sql($sql);

			$result = $debugImitateQuery || $deployer->dbQuery($sql, 'Error running query!');

			if(!$result) {
				$deployer->logMessage($result);
				sql($sql);
				break;
			}
		}
	}

	$deployer->logMessage("$recordsToRemove records were removed");
}


$deployer->logMessage("\nFile $fileName processed completely, rows = $rowsTotal, rows imported = $rowsProcessedTotal, total rows imported = $rowsInsertedTotal\n");
