<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
/* Copyright (C) 2001-2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2015 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2012 Regis Houssin        <regis.houssin@inodbox.com>
 * Copyright (C) 2015      Jean-François Ferry	<jfefe@aternatik.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 *	\file       reports/selection.php
 *	\ingroup    reports
 *	\brief      Home page of reports top menu
 */

// Load Dolibarr environment

$res = 0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) {
	$res = @include $_SERVER["CONTEXT_DOCUMENT_ROOT"] . "/main.inc.php";
}
// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
$tmp = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME'];
$tmp2 = realpath(__FILE__);
$i = strlen($tmp) - 1;
$j = strlen($tmp2) - 1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] === $tmp2[$j]) {
	$i--;
	$j--;
}
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1)) . "/main.inc.php")) {
	$res = @include substr($tmp, 0, ($i + 1)) . "/main.inc.php";
}
if (!$res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1))) . "/main.inc.php")) {
	$res = @include dirname(substr($tmp, 0, ($i + 1))) . "/main.inc.php";
}
// Try main.inc.php using relative path
if (!$res && file_exists("../main.inc.php")) {
	$res = @include "../main.inc.php";
}
if (!$res && file_exists("../../main.inc.php")) {
	$res = @include "../../main.inc.php";
}
if (!$res && file_exists("../../../main.inc.php")) {
	$res = @include "../../../main.inc.php";
}
if (!$res) {
	die("Include of main fails");
}

require DOL_DOCUMENT_ROOT . '/vendor/autoload.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/functions2.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/html.formother.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/html.formcompany.class.php';
require_once DOL_DOCUMENT_ROOT . '/product/class/html.formproduct.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/files.lib.php';
require_once 'functions.php';
require_once __DIR__.'/class/reports.class.php';


// Load translation files required by the page
$langs->loadLangs(array("reports@reports"));

$action = GETPOST('action', 'aZ09');

// Security check
if (!$user->rights->reports->myobject_list->read) {
	accessforbidden();
}
$socid = GETPOST('socid', 'int');
if (isset($user->socid) && $user->socid > 0) {
	$action = '';
	$socid = $user->socid;
}

$max = 5;
$now = dol_now();


 = $db->database_name;
$reportid = $_GET['reportid'];
$selected_salesperson = GETPOST('userid');
$selected_group = GETPOST('state_id');
$period = '';
$params = [];
$format = ['pdf'];
$validation = false;

/*
 * Actions
 */

// None


/*
 * View
 */

$form = new Form($db);
$formfile = new FormFile($db);
$formcompany = new FormCompany($db);
$formother = new FormOther($db);
$formproduct = new FormProduct($db);


llxHeader("", $langs->trans("Report Parameters"));

print load_fiche_titre($langs->trans("Report Parameters"));

print '<div class="fichecenter"><div class="fichethirdleft">';

print '<h3>REPORT ID: ' . strtoupper($reportid) . '</h3>';

print '<form action="" method="POST">';
print '<table class="noborder centpercent">';

//

// RECEIPTS REPORT
if ($reportid === 'receipts') {
	dateInput($form, $conf);

	print '</tr>';

	//Salesperson
	select_salesperson($selected_salesperson, $formother, $user);

	//Group
	group_select($formcompany, $selected_group);


	$fromdate = format_date(GETPOST('from_date'));
	$todate = format_date(GETPOST('to_date'));
	$selected_group = GETPOST('state_id', 'int');
	$selected_salesperson = GETPOST('userid', 'int');

	if ($todate) {

		$params['FROM_DATE'] = $fromdate;
	}

	if ($todate) {
		$params['TO_DATE'] = $todate;
	}

	if (intval($selected_salesperson > 0)) {
		$params['SALESPERSON'] = $selected_salesperson;
	}

	if (intval($selected_group > 0)) {
		$params['GROUP'] = $selected_group;
	}


	print '</td></tr>';

	//Submit Button
	print '<tr><td>';
	print '<input type="submit" value="submit" name="submit" class="Submit butAction">';
	print '</td>';
	if (isset($_POST['submit'])) {

		if ($fromdate > $todate) {
			setEventMessages($langs->transnoentities('ErrorFieldRequired', $langs->transnoentities('Start date cannot be greater than end date')), null, 'errors');
			$error++;
		} else {

			ReportGen::get_report($params, $reportid, );
		}
	}
}




//STOCK REPORT
elseif ($reportid === 'Stock_Balance') {

	//Warehouse
	print '<tr>';
	print '<td>';
	print 'Warehouse';
	print '</td>';
	print '<td>';
	print $formproduct->selectWarehouses(GETPOST('fk_default_warehouse', 'int'), 'fk_default_warehouse', 'warehouseopen', 1, 0, 0, '', 0, 0, array(), 'minwidth300 widthcentpercentminusxx maxwidth500');
	print '</td>';
	print '</tr>';


	$warehouse = GETPOST('fk_default_warehouse');

	if (intval($warehouse > 0)) {
		$params['WAREHOUSE'] = $warehouse;
	}

	//Submit Button
	print '<tr><td>';
	print '<input type="submit" value="submit" name="submit" class="Submit butAction">';
	print '</td>';
	if (isset($_POST['submit'])) {

		if ($warehouse < 1) {
			setEventMessages($langs->transnoentities('ErrorFieldRequired', $langs->transnoentities('Warehouse')), null, 'errors');
			$error++;
		} else {

			ReportGen::get_report($params, $reportid, );
		}
	}
}

//BALANCES REPORT
elseif ($reportid === 'balances') {

	group_select($formcompany, $selected_group);

	print '<tr>';

	$selected_group = GETPOST('state_id', 'int');

	if ($selected_group > 1) {
		$params = ['GROUP' => $selected_group];
	}

	//Submit Button
	print '<tr><td>';
	print '<input type="submit" value="submit" name="submit" class="Submit butAction">';
	print '</td>';
	if (isset($_POST['submit'])) {

		if ($selected_group < 1 && $selected_group != '') {
			setEventMessages($langs->transnoentities('ErrorFieldRequired', $langs->transnoentities('Group')), null, 'errors');
			$error++;
		} else {
			ReportGen::get_report($params, $reportid, );
		}
	}
}

//OPEN INVOICES
elseif ($reportid === 'OpenInvoices') {

	print '<h4>Leave Group Unselected To Show All</h4>';

	group_select($formcompany, $selected_group);

	$selected_group = GETPOST('state_id', 'int');
	if ($selected_group > 1) {

		if (intval($selected_group > 0)) {
			$params['GROUP'] = $selected_group;
		}

		$format = ['xlsx'];
	}

	//Submit Button
	print '<tr><td>';
	print '<input type="submit" value="submit" name="submit" class="Submit butAction">';
	print '</td>';
	if (isset($_POST['submit'])) {


		ReportGen::get_report($params, $reportid, );
	}
}

//SHOP PAPERS
elseif ($reportid === 'shop_papers') {
	$shop = GETPOST('shop');
	// Category
	print '<tr><td>Category</td>';
	print '<td>';
	print $formother->select_categories('customer', $shop, 'shop', 1);
	print "</td></tr>";

	$shop = GETPOST('shop', 'int');

	if (intval($shop > 0)) {
		$params['SHOP'] = $shop;
	}
	//Submit Button
	print '<tr><td>';
	print '<input type="submit" value="submit" name="submit" class="Submit butAction">';
	print '</td>';

	if (isset($_POST['submit'])) {
		if ($shop < 1 && $shop != '') {
			setEventMessages($langs->transnoentities('ErrorFieldRequired', $langs->transnoentities('Shop')), null, 'errors');
			$error++;
		} else {

			ReportGen::get_report($params, $reportid, );
		}
	}
}

//RECEIPT CHECK
elseif ($reportid === 'receipt_book_check') {
	//From Receipt
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

	$fromreceipt = GETPOST('fromreceipt');
	$toreceipt = GETPOST('toreceipt');

	if ($fromreceipt) {
		$params['START_REC'] = $fromreceipt;
	}

	if ($toreceipt) {
		$params['END_REC'] = $fromreceipt;
	}

	//Submit Button
	print '<tr><td>';
	print '<input type="submit" value="submit" name="submit" class="Submit butAction">';
	print '</td>';
	if (isset($_POST['submit'])) {

		if ($fromreceipt > $toreceipt) {
			setEventMessages($langs->transnoentities('ErrorFieldRequired', $langs->transnoentities('Start receipt cannot be smaller than End recipt')), null, 'errors');
			$error++;
		} else {

			ReportGen::get_report($params, $reportid, );
		}
	}
}

// OVERDUE

elseif ($reportid === 'Overdue') { //From Date

	dateInput($form, $conf);
	//Group
	group_select($formcompany, $selected_group);

	$fromdate = format_date(GETPOST('from_date'));
	$todate = format_date(GETPOST('to_date'));
	$selected_group = GETPOST('state_id', 'int');

	if ($fromdate) {
		$params['FROM_DATE'] = $fromdate;
	}

	if ($todate) {
		$params['TO_DATE'] = $todate;
	}

	if (intval($selected_group > 0)) {
		$params['GROUP'] = $selected_group;
	}


	//Submit Button
	print '<tr><td>';
	print '<input type="submit" value="submit" name="submit" class="Submit butAction">';
	print '</td>';
	if (isset($_POST['submit'])) {

		if ($fromdate > $todate) {
			setEventMessages($langs->transnoentities('ErrorFieldRequired', $langs->transnoentities('Start date cannot be greater than end date')), null, 'errors');
			$error++;
		}
		if ($selected_group < 1 && $selected_group != '') {
			setEventMessages($langs->transnoentities('ErrorFieldRequired', $langs->transnoentities('Group')), null, 'errors');
			$error++;
		} else

		{ReportGen::get_report($params, $reportid, );}
	}
}

//PERCENTAGE_COLLECTION
elseif ($reportid === 'Percentage_Collection') {

	print '<tr>';
	print '<td>From Date</td>';
	print '<td>';
	$fromdate = strtotime(format_date(GETPOST('period')));
	$fromdate = ($fromdate == '' ? (!empty($conf->global->MAIN_AUTOFILL_DATE) ? $fromdate : '') : $fromdate);
	print $form->selectDate($fromdate, 'period', '', '', 0, "", 1, 1,);
	print '</td>';
	print '</tr>';

	$period = format_date(GETPOST('period'));

	if ($period) {
		$params['BASEDATE'] = $period;
	}

	print '</td></tr>';

	//Submit Button
	print '<tr><td>';
	print '<input type="submit" value="submit" name="submit" class="Submit butAction">';
	print '</td>';
	if (isset($_POST['submit'])) {

		ReportGen::get_report($params, $reportid, );
	}
}

//Credit_Notes_Returns
elseif ($reportid === 'Credit_Notes_Returns') { //From Date
	dateInput($form, $conf);

	//Group
	group_select($formcompany, $selected_group);

	$fromdate = format_date(GETPOST('from_date'));
	$todate = format_date(GETPOST('to_date'));
	$selected_group = GETPOST('state_id', 'int');


	if ($fromdate) {
		$params['FROM_DATE'] = $fromdate;
	}

	if ($todate) {
		$params['TO_DATE'] = $todate;
	}

	if (intval($selected_group > 0)) {
		$params['GROUP'] = $selected_group;
	}
	print '</td></tr>';

	//Submit Button
	print '<tr><td>';
	print '<input type="submit" value="submit" name="submit" class="Submit butAction">';
	print '</td>';
	if (isset($_POST['submit'])) {

		if ($fromdate > $todate) {
			setEventMessages($langs->transnoentities('ErrorFieldRequired', $langs->transnoentities('Start date cannot be greater than end date')), null, 'errors');
			$error++;
		}
		if ($selected_group < 1 && $selected_group != '') {
			setEventMessages($langs->transnoentities('ErrorFieldRequired', $langs->transnoentities('Group')), null, 'errors');
			$error++;
		}

		ReportGen::get_report($params, $reportid, );
	}
}

// Finished_Group
elseif ($reportid === 'Finished_Group') {
	dateInput($form, $conf);

	//Group
	group_select($formcompany, $selected_group);

	$fromdate = format_date(GETPOST('from_date'));
	$todate = format_date(GETPOST('to_date'));

	$selected_group = GETPOST('state_id', 'int');

	if ($fromdate) {
		$params['FROM_DATE'] = $fromdate;
	}

	if ($todate) {
		$params['TO_DATE'] = $todate;
	}

	if (intval($selected_group > 0)) {
		$params['GROUP'] = $selected_group;
	}

	//Submit Button
	print '<tr><td>';
	print '<input type="submit" value="submit" name="submit" class="Submit butAction">';
	print '</td>';
	if (isset($_POST['submit'])) {

		if ($fromdate > $todate) {
			setEventMessages($langs->transnoentities('ErrorFieldRequired', $langs->transnoentities('Start date cannot be greater than end date')), null, 'errors');
			$error++;
		} else {
			ReportGen::get_report($params, $reportid, );
		}
	}
}

//Monthly_Collection_Totals
elseif ($reportid === 'Monthly_Collection_Totals') { //From Date
	dateInput($form, $conf);

	$fromdate = format_date(GETPOST('from_date'));
	$todate = format_date(GETPOST('to_date'));


	if ($fromdate && $todate) {
		$params = ['FROM_DATE' => $fromdate, 'TO_DATE' => $todate];
	}

	print '</td></tr>';

	//Submit Button
	print '<tr><td>';
	print '<input type="submit" value="submit" name="submit" class="Submit butAction">';
	print '</td>';
	if (isset($_POST['submit'])) {

		if ($fromdate > $todate) {
			setEventMessages($langs->transnoentities('ErrorFieldRequired', $langs->transnoentities('Start date cannot be greater than end date')), null, 'errors');
			$error++;
		}

		ReportGen::get_report($params, $reportid, );
	}
}

// SALES
elseif ($reportid === 'Sales_Invoices') {
	dateInput($form, $conf);

	//Salesperson
	select_salesperson($selected_salesperson, $formother, $user);

	//Group
	group_select($formcompany, $selected_group);

	$fromdate = format_date(GETPOST('from_date'));
	$todate = format_date(GETPOST('to_date'));
	$selected_group = GETPOST('state_id', 'int') < 1 ? null : GETPOST('state_id', 'int');
	$selected_salesperson = GETPOST('userid', 'int') < 1 ? null : GETPOST('userid', 'int');



	if ($todate) {

		$params['FROM_DATE'] = $fromdate;
	}

	if ($todate) {
		$params['TO_DATE'] = $todate;
	}

	if (intval($selected_salesperson > 0)) {
		$params['SALESPERSON'] = $selected_salesperson;
	}

	if (intval($selected_group > 0)) {
		$params['GROUP'] = $selected_group;
	}


	print '</td></tr>';

	//Submit Button
	print '<tr><td>';
	print '<input type="submit" value="submit" name="submit" class="Submit butAction">';
	print '</td>';
	if (isset($_POST['submit'])) {

		if ($fromdate > $todate) {
			setEventMessages($langs->transnoentities('ErrorFieldRequired', $langs->transnoentities('Start date cannot be greater than end date')), null, 'errors');
			$error++;
		} else {

			ReportGen::get_report($params, $reportid, );
		}
	}
}

// recietps mobile

elseif ($reportid === 'receipts_mobile') {
	dateInput($form, $conf);

	//Salesperson
	select_salesperson($selected_salesperson, $formother, $user);

	//Group
	group_select($formcompany, $selected_group);

	$fromdate = format_date(GETPOST('from_date'));
	$todate = format_date(GETPOST('to_date'));
	$selected_group = GETPOST('state_id', 'int') < 1 ? null : GETPOST('state_id', 'int');
	$selected_salesperson = GETPOST('userid', 'int') < 1 ? null : GETPOST('userid', 'int');





	if ($todate) {

		$params['FROM_DATE'] = $fromdate;
	}

	if ($fromdate) {
		$params['TO_DATE'] = $todate;
	}

	if (intval($selected_salesperson > 0)) {
		$params['SALESPERSON'] = $selected_salesperson;
	}

	if (intval($selected_group > 0)) {
		$params['GROUP'] = $selected_group;
	}

	//Submit Button
	print '<tr><td>';
	print '<input type="submit" value="submit" name="submit" class="Submit butAction">';
	print '</td>';
	if (isset($_POST['submit'])) {

		if ($fromdate > $todate) {
			setEventMessages($langs->transnoentities('ErrorFieldRequired', $langs->transnoentities('Start date cannot be greater than end date')), null, 'errors');
			$error++;
		} else {

			ReportGen::get_report($params, $reportid, );
		}
	}
}

//OPEN CHECKPOINTS
elseif ($reportid === 'Open_Check_Points') { //From Date
	dateInput($form, $conf);

	//Group
	group_select($formcompany, $selected_group);

	$fromdate = format_date(GETPOST('from_date'));
	$todate = format_date(GETPOST('to_date'));
	$selected_group = GETPOST('state_id', 'int');

	if ($todate) {

		$params['FROM_DATE'] = $fromdate;
	}

	if ($fromdate) {
		$params['TO_DATE'] = $todate;
	}


	if (intval($selected_group > 0)) {
		$params['GROUP'] = $selected_group;
	}

	if ($selected_group < 1 && $selected_group != '') {
		setEventMessages($langs->transnoentities('ErrorFieldRequired', $langs->transnoentities('Group')), null, 'errors');
		$error++;
	}


	//Submit Button
	print '<tr><td>';
	print '<input type="submit" value="submit" name="submit" class="Submit butAction">';
	print '</td>';
	if (isset($_POST['submit'])) {
		if ($selected_group < 1 && $selected_group != '') {
			setEventMessages($langs->transnoentities('ErrorFieldRequired', $langs->transnoentities('Group')), null, 'errors');
			$error++;
		} else {
			ReportGen::get_report($params, $reportid, );
		}
	}
}


// CASHFLOW DAILY TOTALS

elseif ($reportid === 'cashflow_daily_totals') {


	$sql = "SELECT to_char(generate_series(now()- INTERVAL '1 year', now(), '1 month'), 'Mon-YYYY') AS \"Period\",generate_series(now()- INTERVAL '1 year', now(), '1 month') as monthp ORDER BY \"monthp\" DESC";
	$resql = $db->query($sql);

	if ($resql) {
		$total = 0;
		$num = $db->num_rows($resql);

		if ($num > 0) {
			$i = 0;
			print '<tr><td>Select Period</td>';
			print '<td><select name="period" id=period>';
			print '<option value="0">Select Period</option>';
			while ($i < $num) {

				$obj = $db->fetch_object($resql);
				print '<option value="' . $obj->Period . '" ' . (GETPOST('period') === $obj->monthp ? 'selected="selected"' : '') . '>' . $obj->Period . '</option>';
				print '</option>';
				$i++;
			}
		}

		$period = GETPOST('period');
		if (intval($period !== "")) {
			$params['PAYPERIOD'] = $period;
		}

		//Submit Button
		print '<tr><td>';
		print '<input type="submit" value="submit" name="submit" class="Submit butAction">';
		print '</td>';
		if (isset($_POST['submit'])) {
			if ($period === "0") {
				setEventMessages($langs->transnoentities('ErrorFieldRequired', $langs->transnoentities('Period')), null, 'errors');
				$error++;
			} else {
				ReportGen::get_report($params, $reportid, );
			}
		}
	}
}

//Unclassified Invoices
elseif ($reportid === 'Unclassified_Invoices') {
	$sql = 'with x as(select fk_facture_source,	sum(total_ttc) cr_tot from llx_facture group by fk_facture_source)';
	$sql .= ' select f.rowid as invoiceid, f.ref as invoice, date(f.date_valid) invoice_date, s.rowid as customerid, s.nom customer, s.address,';
	$sql .= ' d.nom area, coalesce(pr."label",fd.description) AS product,';
	$sql .= ' max(date(p.datep)) last_pay, date(f.date_lim_reglement) AS due_date,';
	$sql .= ' round(f.total_ttc+coalesce(x.cr_tot,0)) price, SUM(COALESCE(round(pf.amount),0)) paid,';
	$sql .= ' round(f.total_ttc+coalesce(x.cr_tot,0)) - SUM(COALESCE(round(pf.amount),0))  as balance';
	$sql .= ' FROM ' . MAIN_DB_PREFIX . 'facture f';
	$sql .= ' left join llx_societe s on f.fk_soc = s.rowid';
	$sql .= ' LEFT JOIN llx_paiement_facture AS pf ON f.rowid = pf.fk_facture';
	$sql .= ' LEFT JOIN llx_paiement AS p ON pf.fk_paiement = p.rowid';
	$sql .= ' LEFT JOIN llx_facturedet AS fd ON f.rowid = fd.fk_facture';
	$sql .= ' LEFT JOIN llx_product AS pr ON  fd.fk_product = pr.rowid';
	$sql .= ' left join x on f.rowid = x.fk_facture_source';
	$sql .= ' left join llx_c_departements d on d.rowid = s.fk_departement';
	$sql .= ' where f.paye = 0';
	$sql .= ' GROUP by f.rowid, x.cr_tot, pr."label", s.rowid, s.nom, s.name_alias,';
	$sql .= ' s.address, d.nom, fd.description ';
	$sql .= ' having round(f.total_ttc+coalesce(x.cr_tot,0)) - SUM(COALESCE(round(pf.amount),0)) <= 0';
	$sql .= ' ORDER BY s.town, s.address, s.nom';

	$result = $db->query($sql);
	$rows = $db->num_rows($result);
	$row = 0;




	print '<tr class="liste_titre">';
	print '<th colspan="10">Invoice & Credits Note With Issues : ' . ($rows ? '<span class="badge marginleftonlyshort">' . $rows . '</span>' : '') . '</th></tr>';
	print '<tr class="liste_titre">';
	print '<th >Ref</th>';
	print '<th >Customer</th>';
	print '<th >Address</th>';
	print '<th >Group</th>';
	print '<th >Last Pay</th>';
	print '<th >Due Date</th>';
	print '<th >Price</th>';
	print '<th >Paid</th>';
	print '<th >Balance</th>';

	while ($row < $rows) {
		$obj = $db->fetch_object($result);
		print '<tr class="oddeven">';
		print '<td ><a href="' . DOL_URL_ROOT . '/compta/facture/card.php?facid=' . $obj->invoiceid . '">' . $obj->invoice . '</a></td>';
		print '<td ><a href="' . DOL_URL_ROOT . '/comm/card.php?socid=' . $obj->customerid . '">' . $obj->customer . '</a></td>';
		print '<td >' . $obj->address . '</td>';
		print '<td >' . $obj->area . '</td>';
		print '<td >' . $obj->last_pay . '</td>';
		print '<td >' . $obj->due_date . '</td>';
		print '<td >' . price($obj->price) . '</td>';
		print '<td >' . price($obj->paid) . '</td>';
		print '<td >' . price($obj->balance) . '</td>';
		$row++;
	}

} else

//NOT IMPLEMENTED
{
	print '<h4>Report not yet implemented</h4>';
}





print '</table>';
print '</form>';

print '</div></div></div>';

print '<div class="fichecenter"><div class="fichehalfleft">';
print '<a name="builddoc"></a>';
print '<div>';
print '</div>';
// Generated documents
$filedir = DOL_DATA_ROOT . '/reports/' . dol_sanitizeFileName($reportid) . '/';
$relativepath = $reportid . '/';
$filearray = dol_dir_list($filedir, "files", 0, '', '(\.meta|_preview.*\.png)$', '', '', 1);
$totalsize = 0;
foreach ($filearray as $key => $file) {
	$totalsize += $file['size'];
}

//List of document
@$formfile->list_of_documents(
	$filearray,
	'',
	'reports',
	'&entity=1',
	0,
	$relativepath,
	0,
	2,
	'No document found',
	0,
	'',
	'',
	0,
	'',
	'',
	'',
	'',
	1,
	0,
	0

);


// End of page
llxFooter();
$db->close();