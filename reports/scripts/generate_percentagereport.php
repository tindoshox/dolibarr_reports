<?php
require_once __DIR__ . '/../../../main.inc.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/admin.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/functions2.lib.php';
require_once __DIR__ . '/../class/pdf_percentagereport.class.php';
require_once __DIR__ . '/../lib/functions.php';


function generatePercentageReport($db,$startperiod, $reportId): ?string
{
	global $conf, $langs;
	$data = fetchPercentageData($db, $startperiod);

	$pdf = new PercentageReportPDF('L', 'mm', 'A4');
	$pdf->title = strtoupper("CUSTOMER PAYMENT PERFORMANCE: $startperiod");
	$pdf->conf = $conf;
	$pdf->langs = $langs;
	$pdf->SetCreator(DOL_APPLICATION_TITLE);
	$pdf->SetMargins(10, 20, 10);
	$pdf->SetAutoPageBreak(true, 20);

	$pdf->renderReport($data);

	// Save file to documents
return	saveFileToDocuments($pdf,$reportId,'.pdf',$startperiod);


}
