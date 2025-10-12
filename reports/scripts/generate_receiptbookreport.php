<?php

require_once __DIR__.'/../../../main.inc.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/admin.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/functions2.lib.php';
require_once __DIR__ . '/../class/pdf_receiptbook.class.php';
require_once __DIR__ . '/../lib/functions.php';

$langs->load("reports");

function generateReceiptbookReport($db, $startreceipt, $endreceipt, $reportId): ?string
{
	$data = fetchReceiptBookData($db, $startreceipt, $endreceipt);

	//Create and render PDF
	$pdf = new ReceiptBookPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
	$pdf->startreceipt = $startreceipt;
	$pdf->endreceipt = $endreceipt;
	$pdf->SetCreator(DOL_APPLICATION_TITLE);

	$pdf->renderReport($data);

	// Save file to documents
return	saveFileToDocuments($pdf,$reportId,'.pdf');


}
