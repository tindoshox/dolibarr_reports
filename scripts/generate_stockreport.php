<?php

require_once __DIR__ . '/../../../main.inc.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/admin.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/functions2.lib.php';
require_once __DIR__ . '/../class/pdf_stockreport.class.php';
require_once __DIR__ . '/../lib/functions.php';

$langs->load("reports");

function generateStockReport($db,$warehouseId,$reportId): ?array
{
	$data = fetchStockData($db,$warehouseId);


	//Create and render
	$pdf = new StockReportPDF('P',PDF_UNIT,PDF_PAGE_FORMAT,true,'UTF8, false');

	$pdf->SetCreator(DOL_APPLICATION_TITLE);
	$pdf->SetAutoPageBreak(true, 20);

	$pdf->renderReport($data);

	// Save file to documents
return	saveFileToDocuments($pdf,$reportId,'.pdf');


}
