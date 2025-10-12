<?php

require_once __DIR__ . '/../../../main.inc.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/admin.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/functions2.lib.php';
require_once __DIR__ . '/../class/pdf_returnsreport.class.php';
require_once __DIR__ . '/../lib/functions.php';

$langs->load("reports");

function generateReturnsReport($db, $startdate, $enddate,$stateId,$reportId): ?string
{
	//Fetch Data
	$data = fetchReturnsData($db, $startdate, $enddate,  $stateId);

	//Create and render
	$pdf = new ReturnsReportPDF('L',PDF_UNIT,PDF_PAGE_FORMAT,true,'UTF8, false');
	$pdf->startdate = $startdate;
	$pdf->enddate = $enddate;

	$pdf->SetCreator(DOL_APPLICATION_TITLE);
	$pdf->SetAutoPageBreak(true, 20);

	$pdf->renderReport($data);

	// Save file to documents
return	saveFileToDocuments($pdf,$reportId,'.pdf');




}
