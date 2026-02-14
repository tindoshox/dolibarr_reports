<?php

require_once __DIR__.'/../../../main.inc.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/admin.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/functions2.lib.php';
require_once __DIR__ . '/../class/pdf_monthlytotals.class.php';
require_once __DIR__ . '/../lib/functions.php';

function generateMonthlyTotalsReport($db, $startperiod, $endperiod,$reportId): ?array
{
	// Fetch Data

	$data = fetchMonthlyTotalData($db, $startperiod, $endperiod);


	// Create and render PDF
	$pdf = new MonthlyTotalsReportPDF('L', 'mm', 'A4');
	$pdf->reportTitle = strtoupper("monthly totals $startperiod to $endperiod");
	$pdf->AddPage();

	// Inject your data
	$pdf->dates = getCrosstabHeaders($data, 'date');
	$pdf->states = getCrosstabHeaders($data, 'state');
	$pdf->data = $data;

	$pdf->renderCrosstab();

	// Save file to documents
return	saveFileToDocuments($pdf,$reportId, '.pdf',$startperiod.'_'. $endperiod);


}


