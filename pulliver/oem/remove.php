<?php

ini_set('memory_limit', '1024M');
ini_set('max_execution_time', '600');
set_time_limit(0);

define('DEPLOY_LOG_FILE', 'oem-import.log');

define('IMPORT_MODE_REMOVE', 1);
define('IMPORT_MODE_UPDATE', 2);

$currentDir = dirname(__FILE__);
$rootDir = dirname(dirname($currentDir)).'/';

require $rootDir . 'Vic.php';

$sqlDump = (isset($_GET['sqldump']) && $_GET['sqldump']);

require_once $currentDir . '/Deployer.php';
$deployer = new Deployer();
$deployer->initLog($rootDir . 'var/log/' . DEPLOY_LOG_FILE);

require_once $currentDir . '/creds.php';

if($deployer->initDb($dbhost, $dbUser, $dbPass, $dbName)) {
    $deployer->logMessage('DB init successful');
} else {
    die('DB init not successful');
}

$files = array();

$importDir = $rootDir . 'var/import/pulliver/oem/';

if($handle = opendir($importDir)) {
    while($file = readdir($handle)) {
		$pathinfo = pathinfo($file);
		if(	('csv' == @$pathinfo['extension'])
		&&	(0 === strpos($file, 'remove-'))
		) {
			$files[] = $file;
		}
	}
    closedir($handle);
} else {
	die('cannot open source directory');
}
sort($files);
vd($files);

// and here the magic begins!
$rowsInsertedTotal = 0;
foreach($files as $file) {
	$rowsTotal = 0;
	$rowsProcessed = 0;
	$rowsProcessedTotal = 0;
	$dataString = '';

	$fileName = $importDir . $file;

	$deployer->logMessage("\nProcessing $fileName");

	if(false !== $handle = fopen($fileName, "rb")) {
		$columns = fgetcsv($handle);

		$pnPos = array_search('part_number', $columns);
		$scPos = array_search('supplier_code', $columns);
		if((false === $pnPos) || (false === $scPos)) {
			$deployer->logMessage("\nCould not find part_number or supplier_code columns in $fileName");
			continue;
		}

		while(true) {
			$rowData = fgetcsv($handle);
			if(false === $rowData) {
				$deployer->logMessage("\nDeleting records by $fileName completed, total rows processed = $rowsTotal, total rows imported = $rowsProcessedTotal");
				break;
			} else if(
					isset($rowData[$pnPos]) && $rowData[$pnPos]
				&&	isset($rowData[$scPos]) && $rowData[$scPos]
			) {
				$sql = 'DELETE FROM `oem_cost` WHERE part_number="'	. addslashes($rowData[$pnPos])
					. '" AND supplier_code="' . addslashes($rowData[$scPos]) . '"';

				if($sqlDump) sql($sql);

				$result = $deployer->dbQuery($sql, 'Error running query!');
				if($result) {
					echo "Deleted: part_number=$rowData[$pnPos], supplier_code=$rowData[$scPos]<br/>";
				} else {
					$deployer->logMessage("\nError deleting record: part_number='$rowData[$pnPos]', supplier_code='$rowData[$scPos]'");
					fclose($handle);
					continue;
				}
				$rowsProcessedTotal += $rowsProcessed;
				$rowsRemovedTotal += $rowsProcessed;
				$rowsProcessed = 0;
			}
			$rowsTotal++;
			$rowsProcessed++;
		}
		fclose($handle);

		$deployer->logMessage("\nFile $file processed completely, rows = $rowsTotal, rows removed = $rowsProcessedTotal, total rows removed = $rowsRemovedTotal\n");
	}

}

$deployer->logMessage("\n\nAll files processed, total rows removed = $rowsInsertedTotal");

?>