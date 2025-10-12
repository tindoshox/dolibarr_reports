<?php


require_once __DIR__.'/../../../main.inc.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/admin.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/functions2.lib.php';
require_once __DIR__ . '/../class/pdf_overdueinvoicereport.class.php';
require_once __DIR__ . '/../lib/functions.php';

$langs->load("reports");

// Only for admin users or appropriate permission
//if (!$user->admin) accessforbidden();

// Get province from GET or POST
//$stateId = GETPOST('provinceid', 'int');
//if (!$stateId) {
//	setEventMessages("Missing province ID", null, 'errors');
//	exit;
//}


function generateOverdueReport($db, $stateId, $startdate, $enddate,$reportId): ?array
{
// Fetch data
	list($data, $payments, $agenda) = fetchOverdueInvoicesData($db, $stateId, $startdate, $enddate);

// Get province name from first row
	$provinceName = count($data) ? $data[0]['areagroup'] : '';

// Create and render PDF
	$pdf = new OverdueInvoiceReportPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
	$pdf->provinceName = $provinceName;
	$pdf->startDate = date('d-m-Y', strtotime($startdate));
	$pdf->endDate = date('d-m-Y', strtotime($enddate));
	$pdf->SetCreator(DOL_APPLICATION_TITLE);
	$pdf->SetMargins(10, 20, 10);
	$pdf->SetAutoPageBreak(true, 20);

	$pdf->renderReport($data, $payments, $agenda);

    // Save file to documents
return	saveFileToDocuments($pdf,$reportId,'.pdf');



}
