<?php


require_once __DIR__.'/../../../main.inc.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/admin.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/functions2.lib.php';
require_once __DIR__ . '/../class/pdf_balancesreport.class.php';
require_once __DIR__ . '/../lib/functions.php';

$langs->load("admin");

// Only for admin users or appropriate permission
//if (!$user->admin) accessforbidden();

// Get province from GET or POST
//$stateId = GETPOST('provinceid', 'int');
//if (!$stateId) {
//	setEventMessages("Missing province ID", null, 'errors');
//	exit;
//}


function generateBalancesReport($db, $stateId, $reportId): array|null
{
	// Run generator
	list($data, $payments, $agenda) = fetchInvoiceReportData($db, $stateId);

	$pdf = new BalancesReportPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
	$pdf->SetCreator(DOL_APPLICATION_TITLE);
	//$pdf->SetAuthor($user->getFullName($langs));
	$pdf->SetTitle("Customer Invoice Report");
	$pdf->SetMargins(10, 20, 10);
	$pdf->SetAutoPageBreak(true, 15);

	$pdf->renderReport($data, $payments, $agenda);

	$params = getStateNames($db,$stateId);
	// Save file to documents
	return saveFileToDocuments($pdf,$reportId,'.pdf', $params);



}
