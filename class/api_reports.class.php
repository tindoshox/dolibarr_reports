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

use Luracast\Restler\RestException;
use Luracast\Restler\Format\UploadFormat;

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
	 * @var array   $DOCUMENT_FIELDS     Mandatory fields, checked when create and update object
	 */
	public static $DOCUMENT_FIELDS = array(
		'documentid'
	);

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
	 * Test sample 1: { "reportid": "groupid","salesperson": "salesperson", "fromdate": "fromdate", "todate": "todate" }.
	 *
	 * Supported modules: reports
	 *
	 * @param	string	$reportid 	{@from body}
	 * @param	int	$groupid		{@from body}
	 * @param	int $salesperson	{@from body}
	 * @param	string $fromdate		{@from body}
	 * @param	string $todate 		{@from body}
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


	public function builddoc($reportid, $groupid = 0, $salesperson = 0, $fromdate = '', $todate = '')
	{
		global $conf, $langs;
		 = $this->db->database_name;
		$modulepart = 'reports';

		$original_file = "$reportid/$reportid.pdf";
		//--- Finds and returns the document
		$entity = $conf->entity;


		$relativefile = $original_file;

		$check_access = dol_check_secure_access_document($modulepart, $relativefile, $entity, DolibarrApiAccess::$user, '', '');
		$accessallowed              = $check_access['accessallowed'];
		$sqlprotectagainstexternals = $check_access['sqlprotectagainstexternals'];
		$original_file              = $check_access['original_file'];

		if (preg_match('/\.\./', $original_file) || preg_match('/[<>|]/', $original_file)) {
			throw new RestException(401);
		}
		// if (!$accessallowed) {
		// 	throw new RestException(401);
		// }


		// --- Generates the document

		require_once __DIR__ . '/reports.class.php';


		
		if ($fromdate != '') {
			$params['FROM_DATE'] = $fromdate;
		}

		if ($todate != '') {

			$params['TO_DATE'] = $todate;
		}

		if (intval($salesperson > 0)) {
			$params['GROUP'] = $groupid;
		}

		if (intval($groupid > 0)) {
			$params['GROUP'] = $groupid;
		}

		$result = ReportGen::get_report($params, $reportid, );
		if ($result <= 0) {
			throw new RestException(500, $params);
		}
	


		$filename = basename($original_file);
		$original_file_osencoded = dol_osencode($original_file); // New file name encoded in OS encoding charset

		// if (!file_exists($original_file_osencoded)) {
		// 	throw new RestException(404, 'File not found');
		// }

		$file_content = file_get_contents($original_file_osencoded);
		return array('filename' => $filename, 'content-type' => dol_mimetype($filename), 'filesize' => filesize($original_file), 'content' => base64_encode($file_content),  'encoding' => 'base64');
	}



	/**
	 * Return a document.
	 *
	 * @param   int         $id          ID of document
	 * @return  array                    Array with data of file
	 *
	 * @throws RestException
	 */
	/*
	public function get($id) {
		return array('note'=>'xxx');
	}*/




	// phpcs:disable PEAR.NamingConventions.ValidFunctionName
	/**
	 * Validate fields before create or update object
	 *
	 * @param   array           $data   Array with data to verify
	 * @return  array
	 * @throws  RestException
	 */
	private function _validate_file($data)
	{
		// phpcs:enable
		$result = array();
		foreach (Documents::$DOCUMENT_FIELDS as $field) {
			if (!isset($data[$field])) {
				throw new RestException(400, "$field field missing");
			}
			$result[$field] = $data[$field];
		}
		return $result;
	}
}
