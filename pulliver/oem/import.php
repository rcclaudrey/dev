<?php

ini_set('memory_limit', '1024M');
ini_set('max_execution_time', '600');
set_time_limit(0);

define('MAX_QUERY_ROWS', 100);
define('DEPLOY_LOG_FILE', 'oem-import.log');

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
		if('csv' == @$pathinfo['extension']) {
			$files[] = $file;
		}
	}
    closedir($handle);
} else {
	die('cannot open source directory');
}
sort($files);

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
		while(true) {
			$rowData = fgetcsv($handle);
			if(false === $rowData || $rowsProcessed >= MAX_QUERY_ROWS) {
				if($dataString) {
					$sql = 'INSERT INTO `oem_cost`('.implode(',', $columns).') VALUES ' . rtrim($dataString, ',') . ' ON DUPLICATE KEY UPDATE cost=VALUES(cost), part_name=VALUES(part_name)';

					if($sqlDump) sql($sql);

					if(!$result = $deployer->dbQuery($sql, 'Error running query!')) {
						$deployer->logMessage($result);
						sql($sql);
						break;
//						continue;
//						fclose($handle);
//						die;
					}
					$dataString = '';
					$rowsProcessedTotal += $rowsProcessed;
					$rowsInsertedTotal += $rowsProcessed;
					$rowsProcessed = 0;
				}

//				$deployer->logMessage("File $file, total rows processed = $rowsTotal, total rows imported = $rowsProcessedTotal");

				if(false === $rowData) {
					$deployer->logMessage("\nImporting $fileName completed, total rows processed = $rowsTotal, total rows imported = $rowsProcessedTotal");
					break;
				}
			}

			$rowsTotal++;

//			if(count($rowData) != 3) {
//				$deployer->logMessage('wrong data count at row='.$rowsTotal);
//				continue;
//			}

			foreach($rowData as &$col) {
				$col = addslashes($col);
			}

			$dataString .= '(\'' . implode('\',\'', $rowData) . '\'),';
			$rowsProcessed++;
		}
		fclose($handle);

		$deployer->logMessage("\nFile $file processed completely, rows = $rowsTotal, rows imported = $rowsProcessedTotal, total rows imported = $rowsInsertedTotal\n");
	}

}

$deployer->logMessage("\n\nAll files processed, total rows imported = $rowsInsertedTotal");

?>