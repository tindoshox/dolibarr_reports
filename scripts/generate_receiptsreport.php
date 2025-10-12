<?php
require_once __DIR__.'/../../../main.inc.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/admin.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/functions2.lib.php';
require_once __DIR__ . '/../class/pdf_receipts.class.php';
require_once __DIR__ . '/../lib/functions.php';

$langs->load("reports");

function  generateReceiptsReport($db,$stateId,$userId, $startdate, $enddate,$reportId): ?array
{
	//fetch data

	list($data) = fetchReceiptsData($db,$stateId,$userId, $startdate, $enddate);

	$total = 0;
	foreach ($data as $rec){
		$total += $rec['amount'];
	}

	$subtotal = 0;

	//Create and render PDF
	$pdf = new ReceiptsReportPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
	$pdf->startDate = date('d-m-Y', strtotime($startdate));
	$pdf->endDate = date('d-m-Y', strtotime($enddate));
	$pdf->total = $total;
	$pdf->subtotal = $subtotal;
	$pdf->SetCreator(DOL_APPLICATION_TITLE);
	$pdf->SetMargins(10, 20, 10);
	$pdf->SetAutoPageBreak(true, 20);

	$pdf->renderReport($data);

	// Save file to documents
return	saveFileToDocuments($pdf,$reportId,'.pdf');

}
