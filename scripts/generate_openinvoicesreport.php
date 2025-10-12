<?php

spl_autoload_register(function ($class) {
$prefix = 'PhpOffice\\PhpSpreadsheet\\';
$base_dir = DOL_DOCUMENT_ROOT . '/includes/phpoffice/phpspreadsheet/src/PhpSpreadsheet/';

$len = strlen($prefix);
if (strncmp($prefix, $class, $len) !== 0) return;

$relative_class = substr($class, $len);
$file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

if (file_exists($file)) require $file;
});
require_once DOL_DOCUMENT_ROOT . '/includes/Psr/simple-cache/src/CacheInterface.php';
require_once DOL_DOCUMENT_ROOT . '/includes/Psr/simple-cache/src/CacheException.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


/**
 * Export array data to XLSX and stream to browser.
 *
 * @param array $dataArray Array of associative arrays (e.g. from fetchReceiptsData)
 * @param string $filename Desired output filename (e.g. "receipts.xlsx")
 */

require_once __DIR__ . '/../lib/functions.php';
function generateOpenInvoicesReport($db, $stateid, $reportId): ?string

{
	list($data, $payments, $agenda) = fetchInvoiceReportData($db, $stateid);
	$spreadsheet = new Spreadsheet();
	$writer = new Xlsx($spreadsheet);
return	exportArrayToXlsx($spreadsheet,$writer, $data, $reportId);


}



