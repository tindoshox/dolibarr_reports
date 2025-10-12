<?php


use PhpOffice\PhpSpreadsheet\Writer\Xlsx\Rels;


function getReportById(string $reportid): ?ReportModel
{
	$reportList = [
		new ReportModel([
			'reportid' => 'balances',
			'displayName' => 'Balances',
			'hasState' => true,
			'stateIsRequired' => true
		]),
		new ReportModel([
			'reportid' => 'receipts_mobile',
			'displayName' => 'Receipts',
			'hasStartDate' => true,
			'hasEndDate' => true
		]),
		new ReportModel([
			'reportid' => 'stock',
			'displayName' => 'Stock',
			'hasWarehouse' => true
		]),
		new ReportModel([
			'reportid' => 'receipts',
			'displayName' => 'Receipts',
			'hasStartDate' => true,
			'hasEndDate' => true,
			'hasGroup' => true,
			'hasSales' => true,
		]), new ReportModel([
			'reportid' => 'receipts_mobile',
			'displayName' => 'Receipts',
			'hasStartDate' => true,
			'hasEndDate' => true,
			'hasGroup' => true,
			'hasSales' => true,
		]),

		new ReportModel([
			'reportid' => 'daily_totals',
			'displayName' => 'Daily Totals',
			'hasStartPeriod' => true,
		]),
		new ReportModel([
			'reportid' => 'returns',
			'displayName' => 'Returns (Credit Notes)',
			'hasStartDate' => true,
			'hasEndDate' => true,
			'hasState' => true,
		]),
		new ReportModel([
			'reportid' => 'receiptbook',
			'displayName' => 'Receipt Book Check',
			'hasStartReceipt' => true,
			'hasEndReceipt' => true,
		]), new ReportModel([
			'reportid' => 'monthly_totals',
			'displayName' => 'Monthly Totals',
			'hasStartPeriod' => true,
			'hasEndPeriod' => true,
		]), new ReportModel([
			'reportid' => 'overdue',
			'displayName' => 'Overdue',
			'hasStartDate' => true,
			'hasEndDate' => true,
			'hasState' => true,
		]), new ReportModel([
			'reportid' => 'percentage',
			'displayName' => 'Percentage Collection',
			'hasStartPeriod' => true,
		]),
		new ReportModel([
			'reportid' => 'sales',
			'displayName' => 'Sales (Invoices)',
			'hasStartDate'=>true,
			'hasEndDate'=>true,
			'hasState' => true,
			'hasSales' => true,

		]), new ReportModel([
			'reportid' => 'stock',
			'displayName' => 'Stock Count',
			'hasWarehouse' => true,
		]),
		new ReportModel([
			'reportid' => 'open',
			'displayName' => 'Open Invoices',
			'hasState'=>true,
		]),
		new ReportModel([
			'reportid' => 'unclassified',
			'displayName' => 'Unclassified Invoices',
		]),
	];

	foreach ($reportList as $report) {
		if ($report instanceof ReportModel && $report->reportid === $reportid) {
			return $report;
		}
	}
	return null;
}

function dateInput($form, $conf): void
{
	print '<tr>';
	print '<td>From Date</td>';
	print '<td>';
	$startdate = strtotime(format_date(GETPOST('startdate')));
	$startdate = ($startdate == '' ? (!empty($conf->global->MAIN_AUTOFILL_DATE) ? $startdate : '') : $startdate);
	print $form->selectDate($startdate, 'startdate', '', '', 0, "", 1, 1);
	print '</td>';
	print '</tr>';

	//To Date
	print '<tr>';
	print '<td>To Date</td>';
	print '<td>';
	$enddate = strtotime(format_date(GETPOST('enddate')));
	$enddate = ($enddate == '' ? (!empty($conf->global->MAIN_AUTOFILL_DATE) ? $enddate : '') : $enddate);
	print $form->selectDate($enddate, 'enddate', '', '', 0, "", 1, 1);
	print '</td>';
	print '</tr>';
}

// Group Select
function stateSelect($formcompany, $selected_state): void
{
	print '<tr>';
	print '<td>Group</td>';
	print '<td>' . $formcompany->select_state($selected_state, 205, 'stateid') . '</td>';
	print '</td></tr>';
}

// Salesperson select
function salespersonSelect($selected_salesperson, $formother, $user): void
{
	print '<tr>';
	print '<td>Salesperson</td>';
	print '<td>' . $formother->select_salesrepresentatives($selected_salesperson, 'userid', $user, 1) . '</td>';
	print '</tr>';
}

function warehouseSelect($formproduct): void
{
	print '<tr>';
	print '<td>';
	print 'Warehouse';
	print '</td>';
	print '<td>';
	print $formproduct->selectWarehouses(GETPOST('fk_default_warehouse', 'int'), 'fk_default_warehouse', 'warehouseopen', 1, 0, 0, '', 0, 0, array(), 'minwidth300 widthcentpercentminusxx maxwidth500');
	print '</td>';
	print '</tr>';
}

function periodSelect($db, string $label, string $hint, string $id): void
{
	$sql = "SELECT to_char(generate_series(now()- INTERVAL '2 years', now(), '1 month'), 'Mon-YYYY') AS \"Period\",generate_series(now()- INTERVAL '2 years', now(), '1 month') as monthp ORDER BY \"monthp\" DESC";
	$resql = $db->query($sql);

	if ($resql) {
		$total = 0;
		$num = $db->num_rows($resql);

		if ($num > 0) {
			$i = 0;
			print '<tr><td>' . $label . '</td>';
			print '<td><select name=' . $id . ' id=' . $id . '>';
			print '<option value="0">' . $hint . '</option>';
			while ($i < $num) {

				$obj = $db->fetch_object($resql);
				print '<option value="' . $obj->Period . '" ' . (GETPOST('period') === $obj->monthp ? 'selected="selected"' : '') . '>' . $obj->Period . '</option>';
				print '</option>';
				$i++;
			}
		}
	}
}


function receiptInput(){
	print '<tr>';
	print '<td>From Receipt No.</td>';
	print '<td>';
	print '<input type="number" name="fromreceipt" id="fromreceipt" value=' . (GETPOST('fromreceipt')) . ' required>';
	print '</td>';
	print '</tr>';

	//To Receipt
	print '<tr>';
	print '<td>To Receipt No.</td>';
	print '<td>';
	print '<input type="number" name="toreceipt" id="toreceipt" required value=' . (GETPOST('toreceipt')) . '>';
	print '<td>';
	print '</td>';
	print '</tr>';
}



// Change date format
function format_date($originalDate): string
{
	$date = str_replace('/', '-', $originalDate);
	return $originalDate == '' ? '' : strval(date("Y-m-d", strtotime($date)));
}
