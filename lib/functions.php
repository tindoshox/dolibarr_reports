<?php


function exportArrayToXlsx($spreadsheet,$writer, array $dataArray, string $reportId): array|null
{
	if (empty($dataArray)) {
		die("No data to export.");
	}


	$sheet = $spreadsheet->getActiveSheet();

	// Write headers
	$headers = array_keys($dataArray[0]);
	foreach ($headers as $col => $header) {
		$sheet->setCellValueByColumnAndRow($col + 1, 1, ucfirst(str_replace('_', ' ', $header)));
	}

	// Write rows
	$rowIdx = 2;
	foreach ($dataArray as $row) {
		$colIdx = 1;
		foreach ($headers as $header) {
			$sheet->setCellValueByColumnAndRow($colIdx++, $rowIdx, $row[$header]);
		}
		$rowIdx++;
	}

	// Optional: Autofit column width
	foreach (range(1, count($headers)) as $col) {
		$sheet->getColumnDimensionByColumn($col)->setAutoSize(true);
	}

	$filename = $reportId .'.xlsx';
	$relativepath = '/'.$reportId.'/' . $filename;
	$filepath = DOL_DATA_ROOT . '/reports/' . $relativepath;
	if (!is_dir(dirname($filepath))) {
		mkdir(dirname($filepath), 0755, true); // recursively create directories
	}
//	$writer = $writer;
	if (!file_exists($filepath)) {
		return null;
	}
	return ['filepath'=>$filepath,'filename'=>$filename];
}

function saveFileToDocuments($pdf, string $reportId, string $extension, string $params=''): ?array
{

// Save file to documents
	$filename = ucfirst($reportId).'_'. $params.'_'.date('Ymd_His').$extension;
	$relativepath = '/'.$reportId.'/' . $filename;
	$filepath = DOL_DATA_ROOT . '/reports/' . $relativepath;

	dol_mkdir(dirname($filepath));
	$pdf->Output($filepath, 'F');
	if (!file_exists($filepath)) {
		return null;
	}else {
		return ['filepath' => $filepath, 'filename' => $filename];
	}
}

function getCrosstabHeaders(array $data, string $key): array {
	$set = [];
	foreach ($data as $row) {
		if (isset($row[$key])) {
			$set[$row[$key]] = true;
		}
	}
	$headers = array_keys($set);
	sort($headers);
	return $headers;
}

// Fetch report data
function fetchInvoiceReportData($db, $stateId): array
{
	$data = [];
	$payments = [];
	$agenda = [];

	// Fetch invoices and customer info
	$sql = "WITH x AS (SELECT f1.rowid AS id, SUM(f1.total_ttc) AS amount FROM " . MAIN_DB_PREFIX . "facture f1 WHERE f1.fk_facture_source IS NULL AND f1.type = 0 GROUP BY id";
	$sql .= " UNION SELECT f2.fk_facture_source AS id, SUM(f2.total_ttc) AS amount FROM " . MAIN_DB_PREFIX . "facture f2 WHERE f2.fk_facture_source IS NOT NULL GROUP BY id";
	$sql .= " UNION SELECT f3.fk_facture AS id, SUM(f3.amount)*-1 AS amount FROM " . MAIN_DB_PREFIX . "paiement_facture f3 GROUP BY id) SELECT  f.rowid, f.ref AS invoice_num,";
	$sql .= " f.ref_client AS deliver_num, s.rowid AS acid, s.nom, s.name_alias, s.address, s.town, s.phone, s.fax, f.datef AS invoice_date, COALESCE(p.label, fd.description) AS prod,";
	$sql .= " f.date_lim_reglement AS due_date, SUM(x.amount) AS balance FROM " . MAIN_DB_PREFIX . "facture f LEFT JOIN " . MAIN_DB_PREFIX . "societe s ON f.fk_soc = s.rowid";
	$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "facturedet fd ON f.rowid = fd.fk_facture LEFT JOIN " . MAIN_DB_PREFIX . "product p ON fd.fk_product = p.rowid LEFT JOIN x ON f.rowid = x.id";
	$sql .= " WHERE fd.total_ht > 0 AND f.fk_statut = 1";
	if($stateId > 0){
		$sql .= " AND s.fk_departement = " . ((int)$stateId)."";
	}
	$sql .= " GROUP BY f.rowid, p.label, s.rowid, s.nom, s.name_alias, s.address, s.town, s.phone, s.fax, fd.description";
	$sql .= " HAVING SUM(x.amount) > 0 ORDER BY s.town, s.address, s.nom";

	$res = $db->query($sql);
	if ($res) {
		while ($obj = $db->fetch_object($res)) {
			$data[] = [
				'rowid' => $obj->rowid,
				'invoice_num' => $obj->invoice_num,
				'deliver_num' => $obj->deliver_num,
				'acid' => $obj->acid,
				'nom' => $obj->nom,
				'name_alias' => $obj->name_alias,
				'address' => $obj->address,
				'town' => $obj->town,
				'phone' => $obj->phone,
				'fax' => $obj->fax,
				'invoice_date' => $obj->invoice_date,
				'prod' => $obj->prod,
				'due_date' => $obj->due_date,
				'balance' => $obj->balance
			];
		}
	}

	// Fetch the last 6 payments for each invoice
	foreach ($data as $invoice) {
		$invoiceId = (int)$invoice['rowid'];

		$sql = "select pa.datep, pa.num_paiement, ROUND(pa.amount) AS tot 	FROM " . MAIN_DB_PREFIX . "paiement_facture pf	INNER JOIN " . MAIN_DB_PREFIX . "paiement pa ON pa.rowid = pf.fk_paiement";
		$sql .= " WHERE pf.fk_facture = $invoiceId	ORDER BY pa.datep DESC	LIMIT 6";

		$res = $db->query($sql);
		if ($res) {
			while ($pay = $db->fetch_object($res)) {
				$payments[$invoiceId][] = [
					'datep' => $pay->datep,
					'num_paiement' => $pay->num_paiement,
					'tot' => $pay->tot
				];
			}
		}


		// Agenda item
		$sql = "select distinct ac.label, ac.datep	from " . MAIN_DB_PREFIX . "actioncomm as ac	where ac.fk_action = 50	and ac.percent < 100 and ac.fk_element = $invoiceId	order by ac.datep desc limit 1	";
		$res = $db->query($sql);
		if ($res && $db->num_rows($res) > 0) {
			$act = $db->fetch_object($res);
			$agenda[$invoiceId] = strtoupper($act->label) . ": " . date('d-m-Y', strtotime($act->datep));
		}
	}
	return [$data, $payments, $agenda];

}

function fetchOverdueInvoicesData($db, $stateId, $startdate, $enddate): array
{
	$data = [];
	$payments = [];
	$agenda = [];

	$sql = "SELECT s.rowid, s.code_client AS client_id, s.nom, s.name_alias, s.address, s.town, s.phone,   s.fax, f.datef AS invoice_date,  f.ref AS invoice_num, f.ref_client AS deliver_num, f.rowid AS rowid,";
	$sql .= " s.rowid AS acid, COALESCE(pr.label, fd.description) AS prod, ROUND(f.total_ttc) AS inv_tot, f.date_lim_reglement AS due_date, UPPER(d.nom) AS areagroup, ROUND(f.total_ttc) - SUM(COALESCE(ROUND(pf.amount), 0)) AS balance";
	$sql .= " FROM " . MAIN_DB_PREFIX . "societe s LEFT JOIN " . MAIN_DB_PREFIX . "facture f ON f.fk_soc = s.rowid LEFT JOIN " . MAIN_DB_PREFIX . "paiement_facture pf ON f.rowid = pf.fk_facture LEFT JOIN " . MAIN_DB_PREFIX . "paiement p ON pf.fk_paiement = p.rowid";
	$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "facturedet fd ON f.rowid = fd.fk_facture LEFT JOIN " . MAIN_DB_PREFIX . "product pr ON fd.fk_product = pr.rowid JOIN " . MAIN_DB_PREFIX . "c_departements d ON d.rowid = s.fk_departement";
	$sql .= " WHERE f.paye = 0 AND f.date_lim_reglement >= '" . $db->escape($startdate) . "' AND f.date_lim_reglement <='" . $db->escape($enddate) . "'";
	if($stateId>0) {
		$sql .= " AND s.fk_departement = " . ((int)$stateId) . " AND fd.total_ht > 0";
	}
	$sql .= " GROUP BY f.fk_soc, f.datef, f.ref, pr.label, fd.description, f.date_lim_reglement,  s.rowid, s.nom, s.name_alias, s.address, s.town, d.nom, s.phone, s.fax, f.total_ttc, f.rowid, f.ref_client";
	$sql .= " HAVING ROUND(f.total_ttc) - SUM(COALESCE(ROUND(pf.amount), 0)) > 0 ORDER BY s.town, s.address, s.nom";

	$res = $db->query($sql);
	if ($res) {
		while ($obj = $db->fetch_object($res)) {
			$data[] = [
				'rowid' => $obj->rowid,
				'invoice_num' => $obj->invoice_num,
				'deliver_num' => $obj->deliver_num,
				'acid' => $obj->acid,
				'nom' => $obj->nom,
				'name_alias' => $obj->name_alias,
				'address' => $obj->address,
				'town' => $obj->town,
				'phone' => $obj->phone,
				'fax' => $obj->fax,
				'invoice_date' => $obj->invoice_date,
				'prod' => $obj->prod,
				'due_date' => $obj->due_date,
				'balance' => $obj->balance,
				'areagroup' => $obj->areagroup
			];
		}
	}

	// Payments and Agenda (same as your code, just indented cleanly)
	foreach ($data as $invoice) {
		$invoiceId = (int)$invoice['rowid'];

		// Payments
		$sql = "SELECT pa.datep, pa.num_paiement, ROUND(pa.amount) AS tot FROM " . MAIN_DB_PREFIX . "paiement_facture pf INNER JOIN " . MAIN_DB_PREFIX . "paiement pa ON pa.rowid = pf.fk_paiement";
		$sql .= " WHERE pf.fk_facture = $invoiceId ORDER BY pa.datep DESC LIMIT 6";
		$res = $db->query($sql);
		if ($res) {
			while ($pay = $db->fetch_object($res)) {
				$payments[$invoiceId][] = [
					'datep' => $pay->datep,
					'num_paiement' => $pay->num_paiement,
					'tot' => $pay->tot
				];
			}
		}

		// Agenda
		$sql = "SELECT DISTINCT ac.label, ac.datep FROM " . MAIN_DB_PREFIX . "actioncomm AS ac  WHERE ac.fk_action = 50    AND ac.percent < 100 AND ac.fk_element = $invoiceId ORDER BY ac.datep DESC LIMIT 1";
		$res = $db->query($sql);
		if ($res && $db->num_rows($res) > 0) {
			$act = $db->fetch_object($res);
			$agenda[$invoiceId] = strtoupper($act->label) . ": " . date('d-m-Y', strtotime($act->datep));
		}
	}

	return [$data, $payments, $agenda];
}

function fetchReceiptsData($db, $stateId, $userId, $startdate, $enddate): array
{

	$data = [];

	$sql = "with i as (with x as( select fk_facture_source,	sum(total_ttc) cr_tot from " . MAIN_DB_PREFIX . "facture group by fk_facture_source) select f.rowid inv_id, round(f.total_ttc + coalesce(x.cr_tot, 0)) - sum(coalesce(round(pf.amount), 0)) as balance";
	$sql .= " from " . MAIN_DB_PREFIX . "facture f left join " . MAIN_DB_PREFIX . "societe s on f.fk_soc = s.rowid left join " . MAIN_DB_PREFIX . "paiement_facture as pf on f.rowid = pf.fk_facture left join " . MAIN_DB_PREFIX . "paiement as p on pf.fk_paiement = p.rowid";
	$sql .= " left join " . MAIN_DB_PREFIX . "facturedet as fd on f.rowid = fd.fk_facture left join " . MAIN_DB_PREFIX . "product as pr on fd.fk_product = pr.rowid left join x on f.rowid = x.fk_facture_source group by f.rowid,	x.cr_tot)";
	$sql .= " select date(p.datep) tdate, i.balance, s.nom, f.ref, date(f.date_lim_reglement) duedate, s.address, s.town, coalesce(b.num_chq,p.num_paiement) docref, pf.amount famount, coalesce(u.firstname, u.login) firstname, p.datec, date(now())-date(f.date_lim_reglement) as duedays";
	$sql .= " from " . MAIN_DB_PREFIX . "facture f join " . MAIN_DB_PREFIX . "paiement_facture pf on pf.fk_facture = f.rowid join " . MAIN_DB_PREFIX . "paiement p on p.rowid  = pf.fk_paiement join " . MAIN_DB_PREFIX . "user u on p.fk_user_creat = u.rowid";
	$sql .= " join " . MAIN_DB_PREFIX . "societe s on s.rowid = f.fk_soc join " . MAIN_DB_PREFIX . "bank b on b.rowid = p.fk_bank join i on i.inv_id = f.rowid where date(p.datep) between '" . $db->escape($startdate) . "' AND '" . $db->escape($enddate) . "'";
	if ($userId > 0) {
		$sql .= " and p.fk_user_creat = " . ((int)$userId);
	}
	if ($stateId > 0) {
		$sql .= " and s.fk_departement = " . ((int)$stateId);
	}
	$sql .= " order by p.datec desc, p.num_paiement desc";


	$res = $db->query($sql);
	if ($res) {
		while ($obj = $db->fetch_object($res)) {
			$data[] = [
				'transaction_date' => $obj->tdate,
				'balance' => $obj->balance,
				'name' => $obj->nom,
				'invoice_num' => $obj->ref,
				'due_date' => $obj->duedate,
				'address' => $obj->address,
				'town' => $obj->town,
				'rec_num' => $obj->docref,
				'amount' => $obj->famount,
				'user' => $obj->firstname,
				'capture_date' => $obj->datec,
				'due_days' => $obj->duedays,

			];
		}
	}

	return [$data];

}

function fetchDailyTotalData($db, $peroid): array
{
	$payperiod = $peroid;
	$startdate = date('Y-m-01', strtotime("01 $payperiod"));  // e.g. '2025-03-01'
	$enddate = date('Y-m-d', strtotime("$startdate +1 month")); // e.g. '2025-04-01'
	$data = [];

	$sql = "select p.datep, d.nom, ROUND(SUM(pf.amount)) amount from " . MAIN_DB_PREFIX . "facture f join " . MAIN_DB_PREFIX . "paiement_facture pf on pf.fk_facture = f.rowid";
	$sql .= " JOIN " . MAIN_DB_PREFIX . "paiement p ON p.rowid = pf.fk_paiement JOIN " . MAIN_DB_PREFIX . "societe s ON s.rowid = f.fk_soc JOIN " . MAIN_DB_PREFIX . "c_departements d ON d.rowid = s.fk_departement";
	$sql .= " WHERE p.datep >= '" . $db->escape($startdate) . "' AND p.datep < '" . $db->escape($enddate) . "' GROUP BY p.datep, d.nom ORDER BY p.datep, d.nom";

	$res = $db->query($sql);
	if ($res) {
		while ($obj = $db->fetch_object($res)) {
			$data[] = [
				'date' => $obj->datep,
				'state' => $obj->nom,
				'amount' => $obj->amount,
			];
		}
	}

	return $data;

}


function fetchMonthlyTotalData($db, $startperoid, $endperiod): array
{
	$startdate = date('Y-m-01', strtotime("01 $startperoid"));
	$payperiodend = $endperiod;
	$edate = date('Y-m-01', strtotime("01 $payperiodend"));  // e.g. '2025-03-01'
	$enddate = date('Y-m-d', strtotime("$edate +1 month")); // e.g. '2025-04-01'

	$data = [];

	$sql = "SELECT to_char(p.datep, '01-mm-YYYY') period, d.nom, sum( pf.amount) amount FROM " . MAIN_DB_PREFIX . "facture f join " . MAIN_DB_PREFIX . "paiement_facture pf ON pf.fk_facture = f.rowid join " . MAIN_DB_PREFIX . "paiement p";
	$sql .= " ON p.rowid = pf.fk_paiement  join " . MAIN_DB_PREFIX . "societe s ON  s.rowid = f.fk_soc  join " . MAIN_DB_PREFIX . "c_departements d ON d.rowid = s.fk_departement";
	$sql .= " WHERE p.datep >= '".$db->escape($startdate)."' AND p.datep < '".$db->escape($enddate)."' group by d.nom, period order by period";

	$res = $db->query($sql);
	if ($res) {
		while ($obj = $db->fetch_object($res)) {
			$data[] = [
				'date' =>date('Y-m-01', strtotime( $obj->period)),
				'state' => $obj->nom,
				'amount' => $obj->amount,
			];
		}
	}

	return $data;

}


function fetchSalesData($db, $startdate, $enddate, $userId, $stateId): array
{
	$data = [];

	$sql = "select distinct s.nom as customer_name, s.address as address, s.town as town, cd.nom as state, s.phone as phone, f.ref as invoice, f.datef, f.ref_client as delnote,";
	$sql .= " coalesce(uc.firstname,uc.login) as username, fd.total_ttc as amount, coalesce(p.label,fd.description)  as p_label from " . MAIN_DB_PREFIX . "societe as s";
	$sql .= " left join " . MAIN_DB_PREFIX . "c_country as c on s.fk_pays = c.rowid left join " . MAIN_DB_PREFIX . "c_departements as cd on s.fk_departement = cd.rowid, " . MAIN_DB_PREFIX . "facture as f left join " . MAIN_DB_PREFIX . "projet as pj on";
	$sql .= " f.fk_projet = pj.rowid left join " . MAIN_DB_PREFIX . "user as uc on f.fk_user_author = uc.rowid left join " . MAIN_DB_PREFIX . "user as uv on f.fk_user_valid = uv.rowid left join " . MAIN_DB_PREFIX . "facture_extrafields as extra on";
	$sql .= " f.rowid = extra.fk_object, " . MAIN_DB_PREFIX . "facturedet as fd";
	$sql .= " left join " . MAIN_DB_PREFIX . "facturedet_extrafields as extra2 on fd.rowid = extra2.fk_object";
	$sql .= " left join " . MAIN_DB_PREFIX . "product as p on (fd.fk_product = p.rowid)";
	$sql .= " left join " . MAIN_DB_PREFIX . "product_extrafields as extra3 on p.rowid = extra3.fk_object";
	$sql .= " where f.fk_soc = s.rowid  and f.rowid = fd.fk_facture  and f.paye = 0  and f.type=0 and fd.total_ht  > 0";
	$sql .= " and date(f.datef) between '" . $db->escape($startdate) . "' and '" . $db->escape($enddate) . "'";
	if ($userId > 0) {
		$sql .= " and  f.fk_user_author  = " . ((int)$userId);
	}
	if ($stateId > 0) {
		$sql .= " and s.fk_departement = " . ((int)$stateId);
	}

	$sql .= " group by cd.nom, s.town, s.address, s.nom, s.phone, f.ref,  f.ref_client,  f.datef,  uc.firstname,  fd.rowid, fd.description, uc.login,  fd.total_ttc,   p.label";
	$sql .= " order by cd.nom, f.datef, s.town, s.address, s.nom";

	$res = $db->query($sql);
	if ($res) {
		while ($obj = $db->fetch_object($res)) {
			$data[] = [
				'date' => $obj->datef,
				'del_note' => $obj->delnote,
				'invoice' => $obj->invoice,
				'name' => $obj->customer_name,
				'address' => $obj->address,
				'town' => $obj->town,
				'phone' => $obj->phone,
				'amount' => $obj->amount,
				'product' => $obj->p_label,
				'user' => $obj->username,
				'state' => $obj->state,
			];
		}
	}

	return $data;


}

function fetchReturnsData($db, $startdate, $enddate, $stateId): array
{
	$data=[];

	$sql .= " with x as (SELECT f.fk_facture_source, f.datef credit_date, f.total_ttc credit_amount FROM " . MAIN_DB_PREFIX . "facture f left join " . MAIN_DB_PREFIX . "societe s on f.fk_soc = s.rowid";
	$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "facturedet AS fd ON f.rowid = fd.fk_facture LEFT JOIN " . MAIN_DB_PREFIX . "product AS pr ON fd.fk_product = pr.rowid where f.type = 2";
	if ($stateId > 0) {
		$sql .= " and s.fk_departement = ". ((int)($stateId));
	}
	$sql .= " ) SELECT f.ref, s.nom, s.address, s.town, s.phone, s.fax, coalesce(pr.label, fd.description) AS prod, f.datef, x.credit_date, f.total_ttc, x.credit_amount FROM " . MAIN_DB_PREFIX . "facture f";
	$sql .= " left join " . MAIN_DB_PREFIX . "societe s on f.fk_soc = s.rowid LEFT JOIN " . MAIN_DB_PREFIX . "facturedet AS fd ON f.rowid = fd.fk_facture LEFT JOIN " . MAIN_DB_PREFIX . "product AS pr ON fd.fk_product = pr.rowid";
	$sql .= " join x on x.fk_facture_source = f.rowid where f.type = 0 and f.date_closing is not null and f.rowid in (select cr.fk_facture_source rowid from " . MAIN_DB_PREFIX . "facture cr";
	$sql .= " where fk_facture_source is not null) and x.credit_date >= '".$db->escape($startdate)."' and x.credit_date <= '".$db->escape($enddate)."'";

	$res = $db->query($sql);

	while ($obj = $db->fetch_object($res)) {
		$data[]=[
			'ref' => $obj->ref,
			'name' => $obj->nom,
			'address' => $obj->address,
			'town' => $obj->town,
			'phone' => $obj->phone,
			'fax' => $obj->fax,
			'product' => $obj->prod,
			'datef'=>$obj->datef,
			'credit_date'=>$obj->credit_date,
			'total_ttc'=>$obj->total_ttc,
			'credit_amount'=>$obj->credit_amount,

		];
	}

	return $data;
}


function fetchStockData($db, $warehouseId): array
{
	$data = [];

	$sql = "SELECT DISTINCT e.REF AS warehouse,   p.label AS product,   ps.reel AS instock";
	$sql .= " FROM " . MAIN_DB_PREFIX . "product p join " . MAIN_DB_PREFIX . "product_stock ps on ps.fk_product = p.rowid  join " . MAIN_DB_PREFIX . "entrepot  e on e.rowid = ps.fk_entrepot";
	$sql .= " WHERE  p.rowid = ps.fk_product AND ps.fk_entrepot = e.rowid";
	if ($warehouseId > 0) {
		$sql .= " and ps . fk_entrepot = " . ((int)$warehouseId);
	}
	$sql .= " order BY e.REF, P.label, ps.reel";

	$res = $db->query($sql);

	while ($obj = $db->fetch_object($res)) {
		$data[] = [
			'warehouse' => $obj->warehouse,
			'product' => $obj->product,
			'instock' => $obj->instock,
		];
	}

	return $data;

}

function fetchReceiptBookData($db, string $start, string $end): array
{

	$data = [];

	$sql = "select f.ref, s.nom, s.town, pa.num_paiement, round(pf.amount) amount, u.firstname, pa.datep, d.nom as state from " . MAIN_DB_PREFIX . "societe s left join " . MAIN_DB_PREFIX . "facture f on f.fk_soc = s.rowid";
	$sql .= " left join " . MAIN_DB_PREFIX . "paiement_facture pf on pf.fk_facture = f.rowid right join " . MAIN_DB_PREFIX . "paiement pa on pf.fk_paiement = pa.rowid right join " . MAIN_DB_PREFIX . "user u on pa.fk_user_creat=u.rowid";
	$sql .= " join " . MAIN_DB_PREFIX . "bank b on b.rowid = pa.fk_bank join " . MAIN_DB_PREFIX . "c_departements d on d.rowid = s.fk_departement where pa.num_paiement between '{$start}' and '{$end}' order by pa.datep, pa.num_paiement";


	$res = $db->query($sql);

	while ($obj = $db->fetch_object($res)) {
		$data [] = [
			'ref' => $obj->ref,
			'name' => $obj->nom,
			'town' => $obj->town,
			'receipt' => $obj->num_paiement,
			'amount' => $obj->amount,
			'firstname' => $obj->firstname,
			'datep' => $obj->datep,
			'state' => $obj->state
		];
	}

	return $data;
}

function fetchPercentageData($db, $peroid): array
{
	$payperiod = $peroid;
	$startdate = date('Y-m-01', strtotime("01 $payperiod"));  // e.g. '2025-03-01'
	$enddate = date('Y-m-d', strtotime("$startdate +1 month")); // e.g. '2025-04-01'
	$data = [];

	$sql = "select nom, round(sum(ab.paidamount)) paidamount, sum(ab.paiditems) paiditems,sum(ab.openitems) openitems,sum(ab.sales) sales, sum(ab.finished) finished, sum(ab.returns) returns";
	$sql .= " from (select d.nom nom, sum(p.amount) paidamount, count(f.rowid) paiditems,";
	$sql .= " 0 as openitems, 0 as sales, 0 as finished, 0 as returns from " . MAIN_DB_PREFIX . "facture f join " . MAIN_DB_PREFIX . "paiement_facture pf on pf.fk_facture = f.rowid";
	$sql .= " join " . MAIN_DB_PREFIX . "paiement p on p.rowid = pf.fk_paiement join " . MAIN_DB_PREFIX . "societe s on s.rowid = f.fk_soc join " . MAIN_DB_PREFIX . "c_departements d";
	$sql .= " on d.rowid = s.fk_departement where p.datep between '" . $db->escape($startdate) . "' and '" . $db->escape($enddate) . "' group by d.nom union select";
	$sql .= " m.nom nom, 0 as paidamount, 0 as paiditems, 0 as openitems, 0 as sales, count(m.nom) finished, 0 as returns from (with balances as ( select f1.rowid as id, sum(f1.total_ttc) as amount";
	$sql .= " from " . MAIN_DB_PREFIX . "facture f1 where f1.fk_facture_source is null and f1.type = 0 group by id union select f2.fk_facture_source as id, sum(f2.total_ttc) as amount";
	$sql .= " from " . MAIN_DB_PREFIX . "facture f2 where f2.fk_facture_source is not null group by id union select f3.fk_facture as id, sum(f3.amount)*-1 as amount from " . MAIN_DB_PREFIX . "paiement_facture f3";
	$sql .= " group by id) select d.nom from " . MAIN_DB_PREFIX . "paiement p join " . MAIN_DB_PREFIX . "paiement_facture pf on pf.fk_paiement = p.rowid join " . MAIN_DB_PREFIX . "facture f on f.rowid = pf.fk_facture";
	$sql .= " join balances b on b.id = f.rowid join " . MAIN_DB_PREFIX . "societe s on s.rowid = f.fk_soc join " . MAIN_DB_PREFIX . "c_departements d on d.rowid = s.fk_departement join " . MAIN_DB_PREFIX . "facturedet as fd on f.rowid = fd.fk_facture join " . MAIN_DB_PREFIX . "product as pr on fd.fk_product = pr.rowid where f.paye = 1";
	$sql .= " and f.fk_facture_source is null and f.rowid not in (select lf.fk_facture_source from " . MAIN_DB_PREFIX . "facture lf where lf.fk_facture_source notnull) and p.datep between '" . $db->escape($startdate) . "' and '" . $db->escape($enddate) . "'";
	$sql .= " group by f.rowid, d.nom) as m group by m.nom union select n.nom nom, 0 as paidamount, 0 as paiditems, 0 as openitems, count(n.nom) as sales, 0 as finished, 0 as returns from (select";
	$sql .= " d.nom from " . MAIN_DB_PREFIX . "societe as s left join " . MAIN_DB_PREFIX . "c_country as c on s.fk_pays = c.rowid left join " . MAIN_DB_PREFIX . "c_departements as d on s.fk_departement = d.rowid, " . MAIN_DB_PREFIX . "facture as f left join " . MAIN_DB_PREFIX . "projet as pj on";
	$sql .= " f.fk_projet = pj.rowid left join " . MAIN_DB_PREFIX . "user as uc on f.fk_user_author = uc.rowid left join " . MAIN_DB_PREFIX . "user as uv on f.fk_user_valid = uv.rowid left join " . MAIN_DB_PREFIX . "facture_extrafields as extra on f.rowid = extra.fk_object ,";
	$sql .= " " . MAIN_DB_PREFIX . "facturedet as fd left join " . MAIN_DB_PREFIX . "facturedet_extrafields as extra2 on fd.rowid = extra2.fk_object left join " . MAIN_DB_PREFIX . "product as p on (fd.fk_product = p.rowid) left join " . MAIN_DB_PREFIX . "product_extrafields as extra3 on";
	$sql .= " p.rowid = extra3.fk_object where f.fk_soc = s.rowid and f.rowid = fd.fk_facture and f.paye = 0 and f.type=0 and f.datef between '" . $db->escape($startdate) . "'and '" . $db->escape($enddate) . "' and fd.total_ht > 0) as n group by n.nom";
	$sql .= " union select t.nom nom, 0 as paidamount, 0 as paiditems, 0 as sales, 0 as openitems, 0 as finished, count(t.nom) as returns from (SELECT d.nom FROM " . MAIN_DB_PREFIX . "facture f left join " . MAIN_DB_PREFIX . "societe s on f.fk_soc = s.rowid";
	$sql .= " join " . MAIN_DB_PREFIX . "c_departements d on s.fk_departement = d.rowid LEFT JOIN " . MAIN_DB_PREFIX . "paiement_facture AS pf	ON 	f.rowid = pf.fk_facture LEFT join " . MAIN_DB_PREFIX . "paiement AS p ON pf.fk_paiement = p.rowid";
	$sql .= " where f.datef >= '" . $db->escape($startdate) . "'and f.datef < '" . $db->escape($enddate) . "' and f.type = 2 GROUP by f.rowid, s.rowid, d.nom) as t group by t.nom union select o.nom nom, 0 as paidamount, 0 as paiditems, count(o.nom) openitems,";
	$sql .= " 0 as sales, 0 as finished, 0 as returns from (with x as(select fk_facture_source, sum(total_ttc) cr_tot from " . MAIN_DB_PREFIX . "facture group by fk_facture_source) SELECT d.nom FROM " . MAIN_DB_PREFIX . "facture f ";
	$sql .= " left join " . MAIN_DB_PREFIX . "societe s on f.fk_soc = s.rowid join " . MAIN_DB_PREFIX . "c_departements d on s.fk_departement = d.rowid LEFT JOIN " . MAIN_DB_PREFIX . "paiement_facture AS pf	ON 	f.rowid = pf.fk_facture";
	$sql .= " LEFT join " . MAIN_DB_PREFIX . "paiement AS p ON pf.fk_paiement = p.rowid left join x on f.rowid = x.fk_facture_source where f.datef < '" . $db->escape($enddate) . "'and (p.datep < '" . $db->escape($enddate) . "' or p.datep is null)";
	$sql .= " GROUP by f.rowid, x.cr_tot, s.rowid, d.nom, f.total_ttc having round(f.total_ttc+coalesce(x.cr_tot,0)) - SUM(COALESCE(round(pf.amount),0)) > 0 union all select d.nom";
	$sql .= " FROM " . MAIN_DB_PREFIX . "facture f left join " . MAIN_DB_PREFIX . "societe s on f.fk_soc = s.rowid join " . MAIN_DB_PREFIX . "c_departements d on s.fk_departement = d.rowid LEFT JOIN " . MAIN_DB_PREFIX . "paiement_facture AS pf	ON 	f.rowid = pf.fk_facture";
	$sql .= " LEFT join " . MAIN_DB_PREFIX . "paiement AS p ON pf.fk_paiement = p.rowid left join x on f.rowid = x.fk_facture_source where f.datef < '" . $db->escape($startdate) . "'and p.datep between '" . $db->escape($startdate) . "'and '" . $db->escape($enddate) . "'";
	$sql .= " GROUP by f.rowid, x.cr_tot, s.rowid, d.nom, f.total_ttc having round(f.total_ttc+coalesce(x.cr_tot,0)) - SUM(COALESCE(round(pf.amount),0)) <= 0 ) as o";
	$sql .= " GROUP BY o.nom) as ab group by ab.nom order by ab.nom";

	$res = $db->query($sql);
	if ($res) {
		while ($obj = $db->fetch_object($res)) {
			$data[] = [
				'state' => $obj->nom,
				'amount' => $obj->paidamount,
				'paid' => $obj->paiditems,
				'opentiems' => $obj->openitems,
				'sales' => $obj->sales,
				'finished' => $obj->finished,
				'returns' => $obj->returns,
			];
		}
	}
	return $data;

}

function getStateNames($db, $stateid )
{
	$sql = 'select nom from '.MAIN_DB_PREFIX.'c_departements where rowid='.$stateid;

	$res = $db->query($sql);
	if ($res) {
		return $db->fetch_object($res)->nom;
	}
	return '';
}
