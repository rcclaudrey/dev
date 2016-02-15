<?php
ini_set('memory_limit', '1024M');
ini_set('max_execution_time', '600');
set_time_limit(0);
define('MAX_QUERY_ROWS', 100);
define('DEPLOY_LOG_FILE', 'import.log');
$rootDir = dirname(dirname(dirname(__FILE__))) . '/';
require $rootDir . 'Vic.php';
$debugMode = isset($_GET['debug']);
require_once 'Deployer.php';
$deployer = new Deployer();
$deployer->initLog($rootDir . 'var/log/' . DEPLOY_LOG_FILE);
require_once 'creds.php';
@include_once 'credslocal.php'; // to override remote settings with the local ones
if ($debugMode) {
    echo "HOST: $dbhost.<br/>USER: $dbUser<br/>DBNAME: $dbName<br/>";
}
if ($deployer->initDb($dbhost, $dbUser, $dbPass, $dbName)) {
    $deployer->logMessage('DB init successful');
} else {
    $deployer->logMessage('DB init not successful');
    die;
}
$tableFields = array(
    'sku', // this must be the first field
    'd_punlim',
    'd_trocky',
    'd_wpower',
    'd_polaris',
    'd_canam',
    'd_fox',
    'd_hhouse',
    'd_honda',
    'd_kawasaki',
    'd_seadoo',
    'd_suzuki',
    'd_yamaha',
    'd_troylee',
    'd_oakley',
    'd_motonation',
    'd_leatt',
    'd_bellhelm',
);
$sourceDir = $rootDir . 'var/import/pulliver/sku/source';
$files = array();
if ($handle = opendir($sourceDir)) {
    while ($file = readdir($handle)) {
        $pathinfo = pathinfo($file);
        if ('csv' == @$pathinfo['extension']) {
            $files[] = $file;
        }
    }
    closedir($handle);
} else {
    $deployer->logMessage("cannot open source directory $sourceDir");
    die;
}
$rowsInsertedTotal = 0;
foreach ($files as $file) {
    $rowsTotal = 0;
    $rowsProcessed = 0;
    $rowsProcessedTotal = 0;
    $dataString = '';
    $fileName = $sourceDir . '/' . $file;
    $deployer->logMessage("Processing $fileName");
    if (false !== $handle = fopen($fileName, "rb")) {
        $columnRow = fgetcsv($handle);
        $queryFields = array();
        foreach ($columnRow as $columnPosition => $columnName) {
            if (in_array($columnName, $tableFields)) {
                $queryFields[$columnName] = $columnPosition;
            }
        }
        if (!isset($queryFields[$tableFields[0]])) { // if no SKU column found
            $deployer->logMessage("Error importing $fileName, no SKU column found among these: " . implode(',', $columnRow));
            continue;
        }
        $updateFields = $queryFields;
        unset($updateFields['sku']);
        $updateExpr = '';
        foreach ($updateFields as $field => $value) {
            $updateExpr .= sprintf('%s = IF(VALUES(%s)<>"", VALUES(%s), %s),', $field, $field, $field, $field);
        }
        $sqlTemplate = 'INSERT INTO sku (' . implode(',', array_keys($queryFields)) . ') VALUES %values% ON DUPLICATE KEY UPDATE %update%';
        $sqlTemplate = str_replace('%update%', rtrim($updateExpr, ','), $sqlTemplate);
        while (true) {
            $rowData = fgetcsv($handle);
            if (false === $rowData || $rowsProcessed >= MAX_QUERY_ROWS) {
                if ($dataString) {
                    $sql = str_replace('%values%', rtrim($dataString, ','), $sqlTemplate);
                    if ($debugMode) {
                        sql($sql);
                    }
                    $deployer->dbQuery($sql, 'Error running query!');
                    $dataString = '';
                    $rowsProcessedTotal += $rowsProcessed;
                    $rowsInsertedTotal += $rowsProcessed;
                    $rowsProcessed = 0;
                }
                if (false === $rowData) {
                    $deployer->logMessage("Importing $fileName completed, total rows processed = $rowsTotal, total rows imported = $rowsProcessedTotal");
                    break;
                }
            }
            $rowsTotal++;
            $data = array();
            $hasData = false;
            foreach ($queryFields as $sqlName => $columnPosition) {
                if (!isset($rowData[$columnPosition])) {
                    $deployer->logMessage("\nError: no column $columnPosition ($sqlName) at row=" . $rowsTotal);
                    continue;
                }
                $value = ('#N/A' == $rowData[$columnPosition]) ? '' : $rowData[$columnPosition];
                $data[] = addslashes($value);
                if ($tableFields[0] !== $sqlName) {
                    $hasData = $hasData || (bool) $value;
                }
            }
            if (!$hasData) {
                continue;
            }
            $dataString .= '(\'' . implode('\',\'', $data) . '\'),';
            $rowsProcessed++;
        }
        fclose($handle);
        $deployer->logMessage("File $file processed completely, rows = $rowsTotal, rows imported = $rowsProcessedTotal, total rows imported = $rowsInsertedTotal\n");
    }
}
$deployer->logMessage("All files processed, total rows imported = $rowsInsertedTotal");
?>