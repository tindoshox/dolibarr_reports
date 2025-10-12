<?php

require_once __DIR__.'/../../../main.inc.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/admin.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/functions2.lib.php';
require_once __DIR__ . '/../class/pdf_dailytotals.class.php';
require_once __DIR__ . '/../lib/functions.php';

function generateDailyTotalsReport($db, $startperiod, $reportId): ?array
{
	// Fetch Data

	$data = fetchDailyTotalData($db, $startperiod);


	// Create and render PDF
	$pdf = new DailyTotalsReportPDF('L', 'mm', 'A4');
	$pdf->reportTitle = strtoupper("DAILY TOTALS FOR $startperiod");
	$pdf->AddPage();

	// Inject your data
	$pdf->dates = getCrosstabHeaders($data, 'date');
	$pdf->states = getCrosstabHeaders($data, 'state');
	$pdf->data = $data;

	$pdf->renderCrosstab();
return	saveFileToDocuments($pdf, $reportId,'.pdf',$startperiod);


}

/**
 * @param DailyTotalsReportPDF $pdf
 * @return void
 */

