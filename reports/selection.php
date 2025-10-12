<?php
global $conf, $error, $langs, $user, $db;
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
 *    \file       reports/selection.php
 *    \ingroup    reports
 *    \brief      Home page of reports top menu
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

require_once DOL_DOCUMENT_ROOT . '/core/lib/functions2.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/html.formother.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/html.formcompany.class.php';
require_once DOL_DOCUMENT_ROOT . '/product/class/html.formproduct.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/files.lib.php';
require_once __DIR__ . '/lib/ui_components.php';
require_once __DIR__ . '/scripts/generate_balancesreport.php';
require_once __DIR__ . '/scripts/generate_overduereport.php';
require_once __DIR__ . '/scripts/generate_receiptsreport.php';
require_once __DIR__ . '/scripts/generate_receiptsmobilereport.php';
require_once __DIR__ . '/scripts/generate_dailytotalsreport.php';
require_once __DIR__ . '/scripts/generate_salesreport.php';
require_once __DIR__ . '/scripts/generate_stockreport.php';
require_once __DIR__ . '/scripts/generate_receiptbookreport.php';
require_once __DIR__ . '/scripts/generate_percentagereport.php';
require_once __DIR__ . '/scripts/generate_returnsreport.php';
require_once __DIR__ . '/scripts/generate_monthlytotalsreport.php';
require_once __DIR__ . '/scripts/generate_openinvoicesreport.php';
require_once __DIR__ . '/class/data_report.class.php';
require_once __DIR__ . '/lib/generate_report.php';

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


$passkey = $db->database_name;
$reportid = $_GET['reportid'] ?? '';
$selected_salesperson = GETPOST('userid');
$selected_state = GETPOST('stateid');
$startperiod = '';
$format = ['pdf'];
$validation = false;

$form = new Form($db);
$formfile = new FormFile($db);
$formcompany = new FormCompany($db);
$formother = new FormOther($db);
$formproduct = new FormProduct($db);
$modulpart = 'reports';
$report = getReportById($reportid);

llxHeader("", $langs->trans("Report Parameters"));

print load_fiche_titre($langs->trans("Report Parameters"));

print '<div class="fichecenter"><div class="fichethirdleft">';


print '<h3>' . $report->displayName . '</h3>';

print '<form action="" method="POST">';
print '<table class="noborder centpercent">';

print '</tr>';

if ($report->hasEndDate) {
	dateInput($form, $conf);
}

if ($report->hasSales) {
	salespersonSelect($selected_salesperson, $formother, $user);
}

if ($report->hasState) {
	stateSelect($formcompany, $selected_state);
}

if ($report->hasWarehouse) {
	//Warehouse
	warehouseSelect($formproduct);
}

if ($report->hasStartPeriod) {
	periodSelect($db, 'Select Start Period', 'Select Start Period', 'start_period');
}
if ($report->hasEndPeriod) {

	periodSelect($db, 'Select End Period', 'Select End Period', 'end_period');
}

if ($report->hasStartReceipt) {
	receiptInput();
}

print '</td></tr>';

$startdate = format_date(GETPOST('startdate'));
$enddate = format_date(GETPOST('enddate'));
$selected_state = GETPOST('stateid', 'int') < 1 ? 0 : GETPOST('stateid', 'int');
$selected_salesperson = GETPOST('userid', 'int') < 1 ? 0 : GETPOST('userid', 'int');
$warehouse = GETPOST('fk_default_warehouse', 'int') < 1 ? 0 : GETPOST('fk_default_warehouse', 'int');
$startperiod = GETPOST('start_period');
$endperiod = GETPOST('end_period');
$startreceipt = GETPOST('fromreceipt');
$endreceipt = GETPOST('toreceipt');

if ($reportid != 'unclassified') {
	//Submit Button
	print '<tr><td>';
	print '<input type="submit" value="submit" name="submit" class="Submit butAction">';
	print '</td>';
}
if (isset($_POST['submit'])) {

	if ($report->hasStartDate && $startdate > $enddate) {
		setEventMessages($langs->transnoentities('ErrorFieldRequired', $langs->transnoentities('Start date cannot be greater than end date')), null, 'errors');
		$error++;
	}
	if ($report->stateIsRequired && $selected_state < 1 && $selected_state != '') {
		setEventMessages($langs->transnoentities('ErrorFieldRequired', $langs->transnoentities('Group')), null, 'errors');
		$error++;
	}
	if ($report->hasStartReceipt && $startreceipt > $endreceipt) {
		setEventMessages($langs->transnoentities('ErrorFieldRequired', $langs->transnoentities('Start receipt cannot be smaller than End recipt')), null, 'errors');
		$error++;
	}

	if ($report->hasStartPeriod && $startperiod === "0") {
		setEventMessages($langs->transnoentities('ErrorFieldRequired', $langs->transnoentities('Period')), null, 'errors');
		$error++;
	}
	if ($report->hasEndPeriod && $startperiod === "0" || $endperiod === "0") {
		setEventMessages($langs->transnoentities('ErrorFieldRequired', $langs->transnoentities('Period')), null, 'errors');
		$error++;
	} elseif ($report->hasEndPeriod && date('t-m-Y', strtotime($startperiod)) < date('t-m-Y', strtotime($endperiod))) {
		setEventMessages('Start period must be before end period', null, 'errors');
		$error++;
	}
	generateReport($db, $reportid, $selected_state, $selected_salesperson, $startdate, $enddate, $warehouse, $startperiod, $endperiod, $startreceipt, $endreceipt);
}

if ($reportid === 'unclassified') {
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
}

print '</table>';
print '</form>';

print '</div></div>';

// Generated documents
$filedir = DOL_DATA_ROOT . '/reports/' . dol_sanitizeFileName($reportid) . '/';
$relativepath = $reportid . '/';
$filearray = dol_dir_list($filedir, "files", 0, '', '(\.meta|_preview.*\.png)$', 'date', 'desc', 1);


// Handle file deletion
if ($action == 'deletefile') {
	$urlfile = GETPOST('urlfile', 'alpha', 0, null, null, 1);
	$urlfile = basename($urlfile);
	$file = $filedir . (preg_match('/\/$/', $filedir) ? '' : '/') . $urlfile;
	if (file_exists($file)) {
	$ret = dol_delete_file($file);

	if ($ret) {
		setEventMessages($langs->trans("FileWasRemoved", $urlfile), null, 'mesgs');
	} else {
		setEventMessages($langs->trans("ErrorFailToDeleteFile", $urlfile), null, 'errors');
	}
	}
}

// Regenerate $filearray after handling actions
$filearray = dol_dir_list($filedir, "files", 0, '', '(\.meta|_preview.*\.png)$', 'date', 'desc', 1);

//List of document
print $formfile->list_of_documents(
	$filearray,
	null,
	$modulpart,
	'&reportid='.$reportid,
	0,
	$relativepath,
	1,
	3,
	'No document found',
	0,
	'',
	'',
	0,
	0,
	'',
	'',
	'',
	1,
	0,
	0);

print '</div></div>';
// End of page
llxFooter();
$db->close();
