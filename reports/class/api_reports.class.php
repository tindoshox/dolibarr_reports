<?php
/* Copyright (C) 2016   Xebax Christy           <xebax@wanadoo.fr>
 * Copyright (C) 2016	Laurent Destailleur		<eldy@users.sourceforge.net>
 * Copyright (C) 2016   Jean-François Ferry     <jfefe@aternatik.fr>
 * Copyright (C) 2023   Romain Neil             <contact@romain-neil.fr>
 *
 * This program is free software you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

use Luracast\Restler\Format\UploadFormat;
use Luracast\Restler\RestException;

require_once DOL_DOCUMENT_ROOT . '/main.inc.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/files.lib.php';


/**
 * API class for receive files
 *
 * @access protected
 * @class Reports {@requires user,external}
 */
class Reports extends DolibarrApi
{

	/**
	 * @var array $DOCUMENT_FIELDS Mandatory fields, checked when create and update object
	 */
	public static $DOCUMENT_FIELDS = array(
		'documentid'
	);
	private string $filename;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		global $db;
		$this->db = $db;
	}


	/**
	 * Build a document.
	 *
	 * Test sample 1: { "reportid": "groupid","salesperson": "salesperson", "startdate": "startdate", "enddate": "enddate" }.
	 *
	 * Supported modules: reports
	 *
	 * @param string $reportid {@from body}
	 * @param int $groupid {@from body}
	 * @param int $salesperson {@from body}
	 * @param string $startdate {@from body}
	 * @param string $enddate {@from body}
	 * @return  array                   List of documents
	 *
	 * @throws RestException 500 System error
	 * @throws RestException 501
	 * @throws RestException 400
	 * @throws RestException 401
	 * @throws RestException 404
	 *
	 * @url PUT /builddoc
	 */


	public function builddoc(string $reportid, int $groupid = 0, int $salesperson = 0, string $startdate = '', string $enddate = '', int $warehouseId = 0, string $startperiod = '', string $endperiod = '', string $startreceipt = '', string $endreceipt = ''): array
	{
		global $conf, $langs;
		$passkey = $this->db->database_name;
		$modulepart = 'reports';


		//$original_file = "$reportid/$reportid.pdf";
		//--- Finds and returns the document
		$entity = $conf->entity;

//
//		$relativefile = $original_file;
//
//		$check_access = dol_check_secure_access_document($modulepart, $relativefile, $entity, DolibarrApiAccess::$user, '', '');
//		$accessallowed = $check_access['accessallowed'];
//		$sqlprotectagainstexternals = $check_access['sqlprotectagainstexternals'];
//		$original_file = $check_access['original_file'];

//		if (preg_match('/\.\./', $original_file) || preg_match('/[<>|]/', $original_file)) {
//			throw new RestException(401);
//		}
//		 if (!$accessallowed) {
//		 	throw new RestException(401);
//		 }


		// --- Generates the document

		require_once __DIR__ . '/../scripts/generate_dailytotalsreport.php';
		require_once __DIR__ . '/../scripts/generate_balancesreport.php';
		require_once __DIR__ . '/../scripts/generate_monthlytotalsreport.php';
		require_once __DIR__ . '/../scripts/generate_overduereport.php';
		require_once __DIR__ . '/../scripts/generate_percentagereport.php';
		require_once __DIR__ . '/../scripts/generate_receiptbookreport.php';
		require_once __DIR__ . '/../scripts/generate_receiptsmobilereport.php';
		require_once __DIR__ . '/../scripts/generate_receiptsreport.php';
		require_once __DIR__ . '/../scripts/generate_returnsreport.php';
		require_once __DIR__ . '/../scripts/generate_salesreport.php';
		require_once __DIR__ . '/../scripts/generate_stockreport.php';



		switch ($reportid) {

			case "balances":
				$array = generateBalancesReport(db: $this->db, stateId: $groupid,reportId: $reportid);
				if (is_null($array) ) {
					throw new RestException(500, $reportid);
				}
			$this->filename = $array['filepath'];
				break;
			case "sales":
				$array = generateSalesReport(db: $this->db, startdate: $startdate, enddate: $enddate, userId: $salesperson, stateId: $groupid,reportId: $reportid);
				if (is_null($array) ){
					throw new RestException(500, $reportid);
				}
				$this->filename = $array['filepath'];
				break;
			case "stock":
				$array = generateStockReport(db: $this->db, warehouseId: $warehouseId,reportId: $reportid);
				if (is_null($array) ){
					throw new RestException(500, $reportid);
				}

				$this->filename = $array['filepath'];
				break;
			case "receipts":
				$array = generateReceiptsReport(db: $this->db, stateId: $groupid, userId: $salesperson, startdate: $startdate, enddate: $enddate, reportId: $reportid);
				if (is_null($array) ){
					throw new RestException(500, $reportid);
				}
				$this->filename = $array['filepath'];
				break;
			case "receipts_mobile":
				$array = generateReceiptsMobileReport(db: $this->db, stateId: $groupid, userId: $salesperson, startdate: $startdate, enddate: $enddate, reportId: $reportid);
				if (is_null($array) ){
					throw new RestException(500, $reportid);
				}
				$this->filename = $array['filepath'];
				break;
			case "daily_totals":
				$array = generateDailyTotalsReport(db: $this->db, startperiod: $startperiod, reportId: $reportid);
				if (is_null($array) ){
					throw new RestException(500, $reportid);
				}
				$this->filename = $array['filepath'];
				break;
			case "overdue":
				$array = generateOverdueReport(db: $this->db, stateId: $groupid, startdate: $startdate, enddate: $enddate, reportId: $reportid);
				if (is_null($array) ){
					throw new RestException(500, $reportid);
				}
				$this->filename = $array['filepath'];
				break;
			case "monthly_totals":
				$array = generateMonthlyTotalsReport(db: $this->db, startperiod: $startperiod, endperiod: $endperiod, reportId: $reportid);
				if (is_null($array) ){
					throw new RestException(500, $reportid);
				}
				$this->filename = $array['filepath'];
				break;
			case "returns":
				$array = generateReturnsReport(db: $this->db, startdate: $startdate, enddate: $enddate, stateId: $groupid, reportId: $reportid);
				if (is_null($array) ){
					throw new RestException(500, $reportid);
				}
				$this->filename = $array['filepath'];
				break;
			case "percentage":
				$array = generatePercentageReport($this->db, $startperiod, $reportid);
				if (is_null($array) ){
					throw new RestException(500, $reportid);
				}
				$this->filename = $array['filepath'];
				break;
			case "receiptbook":
				$array = generateReceiptbookReport($this->db, $startreceipt, $endreceipt,$reportid);
				if ($array['filepath'] != null){
					throw new RestException(500, $reportid);
				}
				$this->filename = $array['filepath'];
				break;
			default;
				throw new RestException(500, 'Unknown report type');
		}


		$filename =  $this->filename;

		//$filename = basename($original_file);
		$original_file_osencoded = dol_osencode($filename); // New file name encoded in OS encoding charset

		 if (!file_exists($original_file_osencoded)) {
			throw new RestException(404, 'File not found: '. $original_file_osencoded );
		 }

		$file_content = file_get_contents($original_file_osencoded);
		return array('filename' => $filename, 'content-type' => dol_mimetype($filename), 'filesize' => filesize($filename), 'content' => base64_encode($file_content), 'encoding' => 'base64');
	}

}
