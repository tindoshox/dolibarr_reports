<?php
require_once __DIR__.'/../../../main.inc.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/admin.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/functions2.lib.php';
require_once __DIR__ . '/../class/pdf_receiptsmobile.class.php';
require_once __DIR__ . '/../lib/functions.php';

$langs->load("reports");

function  generateReceiptsMobileReport($db,$stateId,$userId, $startdate, $enddate, $reportId): ?array
{
	//fetch data

	list($data) = fetchReceiptsData($db,$stateId,$userId, $startdate, $enddate);

	$total = 0;
	foreach ($data as $rec){
		$total += $rec['amount'];
	}
	$pageLayout = [115,297];
	//Create and render PDF
	$pdf = new ReceiptsMobileReportPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, $pageLayout, true, 'UTF-8', false);
	$pdf->startDate = date('d-m-Y', strtotime($startdate));
	$pdf->endDate = date('d-m-Y', strtotime($enddate));
	$pdf->total = $total;
	$pdf->SetCreator(DOL_APPLICATION_TITLE);
	$pdf->SetMargins(5, 10, 5);
	$pdf->renderReport($data);

	// Save file to documents
return	saveFileToDocuments($pdf,$reportId,'.pdf');

}
