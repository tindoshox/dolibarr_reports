<?php


function generateReport($db, string $reportid, int $groupid = 0, int $salesperson = 0, string $startdate = '', string $enddate = '', int $warehouseId = 0, string $startperiod = '', string $endperiod = '', string $startreceipt = '', string $endreceipt = ''): void
{
	global $langs;
	switch ($reportid) {
		case "balances":
			$array =generateBalancesReport(db: $db, stateId: $groupid, reportId: $reportid);
			$filename = $array['filename'];
			showMessages($filename, $langs, $reportid);
			break;
		case "sales":
			$array = generateSalesReport(db: $db, startdate: $startdate, enddate: $enddate, userId: $salesperson, stateId: $groupid, reportId: $reportid);     
			$filename = $array['filename'];                                                    
			showMessages($filename, $langs, $reportid);
			break;
		case "receipts":
			$array = generateReceiptsReport(db: $db, stateId: $groupid, userId: $salesperson, startdate: $startdate, enddate: $enddate, reportId: $reportid);
			$filename = $array['filename'];
			showMessages($filename, $langs, $reportid);
			break;
		case "receipts_mobile":
			$array = generateReceiptsMobileReport(db: $db, stateId: $groupid, userId: $salesperson, startdate: $startdate, enddate: $enddate, reportId: $reportid);
			$filename = $array['filename'];
			showMessages($filename, $langs, $reportid);
			break;
		case "daily_totals":
			$array = generateDailyTotalsReport(db: $db, startperiod: $startperiod, reportId: $reportid);
			$filename = $array['filename'];
			showMessages($filename, $langs, $reportid);
			break;
		case "overdue":
			$array = generateOverdueReport(db: $db, stateId: $groupid, startdate: $startdate, enddate: $enddate, reportId: $reportid);
			$filename = $array['filename'];
			showMessages($filename, $langs, $reportid);
			break;
		case "monthly_totals":
			$array = generateMonthlyTotalsReport(db: $db, startperiod: $startperiod, endperiod: $endperiod, reportId: $reportid);
			$filename = $array['filename'];
			showMessages($filename, $langs, $reportid);
			break;
		case "returns":
			$array = generateReturnsReport(db: $db, startdate: $startdate, enddate: $enddate, stateId: $groupid, reportId: $reportid);
			$filename = $array['filename'];
			showMessages($filename, $langs, $reportid);
			break;
		case "percentage":
			$array = generatePercentageReport($db, $startperiod, $reportid);
			$filename = $array['filename'];
			showMessages($filename, $langs, $reportid);
			break;
		case "receiptbook":
			$array = generateReceiptbookReport($db, $startreceipt, $endreceipt, $reportid);
			$filename = $array['filename'];
			showMessages($filename, $langs, $reportid);
			break;
		case "open":
			$array = generateOpenInvoicesReport($db, $groupid, $reportid);
			$filename = $array['filename'];
			showMessages($filename, $langs, $reportid);
			break;
		default;
	}
}

function showMessages($filename, $langs, $reportid): void
{
	if ($filename) {

		setEventMessages($langs->trans("File Created: $filename"), null);
	} else {
		setEventMessages($langs->trans("Fail To Create File: " . ucfirst($reportid)), null, 'errors');
	}
}
